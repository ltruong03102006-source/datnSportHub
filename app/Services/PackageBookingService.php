<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingPackage;
use App\Models\BookingPackageSession;
use App\Models\Court;
use App\Models\SlotPrice;
use App\Models\TimeSlot;
use App\Models\Transaction;
use App\Models\VenuePackage;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class PackageBookingService
{
    /**
     * Sinh ngày lặp theo tuần từ một ngày bắt đầu cụ thể.
     *
     * Dùng cho một lịch cố định đã xác định được ngày đầu tiên.
     */
    public function generateDates(Carbon|string $startDate, string $type, int $duration): Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $dates = collect();

        if ($type === 'week') {
            for ($week = 0; $week < $duration; $week++) {
                $dates->push($start->copy()->addWeeks($week));
            }

            return $dates;
        }

        $end = $this->periodEndDate($start, $type, $duration);
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            $dates->push($cursor->copy());
            $cursor->addWeek();
        }

        return $dates;
    }

    /**
     * Sinh ngày theo đúng thứ trong tuần, nằm trong chu kỳ gói.
     *
     * Ví dụ:
     * - Gói tháng từ 01/08 đến 31/08
     * - Chọn đủ 7 thứ trong tuần
     * => sinh đủ 31 buổi.
     */
    public function generateDatesForWeekday(Carbon|string $startDate, int $weekday, string $type, int $duration): Collection
    {
        $periodStart = Carbon::parse($startDate)->startOfDay();
        $periodEnd = $this->periodEndDate($periodStart, $type, $duration);

        $firstDate = $periodStart->dayOfWeek === $weekday
            ? $periodStart->copy()
            : $periodStart->copy()->next($weekday);

        $dates = collect();
        $cursor = $firstDate->copy();

        while ($cursor->lte($periodEnd)) {
            $dates->push($cursor->copy());
            $cursor->addWeek();
        }

        return $dates;
    }

    /**
     * Xem có bị trùng lịch hay không.
     */
    public function checkAvailability(Court $court, TimeSlot $timeSlot, Collection $dates): bool
    {
        return $this->conflictingBookings($court, $timeSlot, $dates)->isEmpty();
    }

    /**
     * Lấy các booking bị trùng.
     */
    public function conflictingBookings(Court $court, TimeSlot $timeSlot, Collection $dates): Collection
    {
        return $this->conflictingBookingsForRange(
            $court,
            $timeSlot->start_time,
            $timeSlot->end_time,
            $dates
        );
    }

    public function conflictingBookingsForRange(Court $court, string $startTime, string $endTime, Collection $dates): Collection
    {
        if ($dates->isEmpty()) {
            return collect();
        }

        return Booking::query()
            ->where('court_id', $court->id)
            ->whereIn('slot_date', $dates->map(fn (Carbon $date) => $date->toDateString())->all())
            ->where('status', 'confirmed')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->orderBy('slot_date')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Tính tiền toàn bộ gói.
     */
    public function calculatePrice(VenuePackage $package, Collection $sessionPlans): array
    {
        $subtotal = $sessionPlans->sum(function (array $plan) {
            if (! array_key_exists('price_per_session', $plan) && isset($plan['time_slot'])) {
                return $plan['dates']->sum(fn (Carbon $date) => $this->priceForDate($plan['time_slot'], $date));
            }

            return (int) $plan['price_per_session'] * $plan['dates']->count();
        });

        $discountAmount = round($subtotal * ((float) $package->discount_percent / 100));
        $finalAmount = max(0, $subtotal - $discountAmount);

        return [
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'final_amount' => $finalAmount,
            'session_count' => $sessionPlans->sum(fn (array $plan) => $plan['dates']->count()),
        ];
    }

    public function previewSessionPlans(
        VenuePackage $package,
        array $sessions,
        Carbon|string $startDate
    ): Collection {
        return $this->buildMultiSlotSessionPlans($package, $sessions, $startDate, false);
    }

    /**
     * Hàm này giữ tên cũ để controller đang gọi createBookings vẫn chạy.
     *
     * Nhưng theo nghiệp vụ mới:
     * - Không sinh Booking con ngay.
     * - Chỉ tạo BookingPackage pending_payment.
     * - Lưu các buổi cố định.
     * - Tạo Transaction pending.
     */
    public function createBookings(
        int $userId,
        VenuePackage $package,
        $sessionsOrCourt,
        $timeSlotOrStartDate = null,
        $startDate = null
    ): BookingPackage {
        if ($sessionsOrCourt instanceof Court) {
            if (! $timeSlotOrStartDate instanceof TimeSlot) {
                throw new RuntimeException('Thiếu khung giờ khi tạo gói đặt sân.');
            }

            $court = $sessionsOrCourt;
            $timeSlot = $timeSlotOrStartDate;
            $start = Carbon::parse($startDate);

            $sessions = [
                [
                    'court_id' => $court->id,
                    'time_slot_id' => $timeSlot->id,
                    'weekday' => $start->dayOfWeek,
                ],
            ];

            return $this->createPendingPackage($userId, $package, $sessions, $start);
        }

        return $this->createPendingPackage(
            $userId,
            $package,
            $sessionsOrCourt,
            $timeSlotOrStartDate
        );
    }

    /**
     * Tạo yêu cầu đăng ký gói, trạng thái pending_payment.
     *
     * Đây là bước đúng sau khi khách bấm "Đăng ký gói".
     */
    public function createPendingPackage(
        int $userId,
        VenuePackage $package,
        array $sessions,
        Carbon|string $startDate
    ): BookingPackage {
        return DB::transaction(function () use ($userId, $package, $sessions, $startDate) {
            $package = VenuePackage::query()
                ->with('venue')
                ->lockForUpdate()
                ->findOrFail($package->id);

            $this->assertPackageIsUsable($package);
            $this->assertPackageHasCapacity($package);
            $this->assertSessionCountIsValid($package, $sessions);

            $sessionPlans = $this->buildMultiSlotSessionPlans($package, $sessions, $startDate, true);

            if ($sessionPlans->isEmpty()) {
                throw new RuntimeException('Gói đặt sân không có buổi hợp lệ.');
            }

            $allDates = $sessionPlans
                ->flatMap(fn (array $plan) => $plan['dates'])
                ->sort()
                ->values();

            if ($allDates->isEmpty()) {
                throw new RuntimeException('Gói đặt sân không sinh được ngày hợp lệ.');
            }

            $price = $this->calculatePrice($package, $sessionPlans);

            $start = Carbon::parse($startDate)->startOfDay();
            $end = $this->periodEndDate($start, $package->type, (int) $package->duration);

            $bookingPackage = BookingPackage::create([
                'user_id' => $userId,
                'venue_id' => $package->venue_id,
                'package_id' => $package->id,
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'weekly_sessions' => $sessionPlans->count(),
                'total_sessions' => $price['session_count'],
                'used_sessions' => 0,
                'total_amount' => $price['subtotal'],
                'discount_amount' => $price['discount_amount'],
                'final_amount' => $price['final_amount'],
                'status' => 'pending_payment',
            ]);

            foreach ($sessionPlans as $index => $plan) {
                $bookingSession = $bookingPackage->sessions()->create([
                    'court_id' => $plan['court']->id,
                    'time_slot_id' => $plan['first_time_slot']->id,
                    'weekday' => $plan['weekday'],
                    'session_order' => $index + 1,
                    'price_per_session' => $plan['price_per_session'],
                ]);

                foreach ($plan['time_slots'] as $slotIndex => $slot) {
                    $bookingSession->slots()->create([
                        'time_slot_id' => $slot['model']->id,
                        'slot_order' => $slotIndex + 1,
                        'price' => $slot['price'],
                    ]);
                }
            }

            $this->createPendingTransaction($bookingPackage, $userId);

            return $this->safeLoad($bookingPackage, [
                'package',
                'sessions.court',
                'sessions.timeSlot',
                'sessions.slots.timeSlot',
                'bookings',
                'transactions',
            ]);
        });
    }

    /**
     * Sau khi thanh toán thành công mới gọi hàm này.
     *
     * Luồng đúng:
     * pending_payment
     * -> payment success
     * -> sinh toàn bộ bookings
     * -> active
     */
    public function activateAfterPayment(
        BookingPackage $bookingPackage,
        ?int $changedBy = null,
        string $transactionStatus = 'success'
    ): BookingPackage {
        return DB::transaction(function () use ($bookingPackage, $changedBy, $transactionStatus) {
            $bookingPackage = BookingPackage::query()
                ->with(['package', 'sessions.court', 'sessions.timeSlot', 'sessions.slots.timeSlot'])
                ->lockForUpdate()
                ->findOrFail($bookingPackage->id);

            if ($bookingPackage->status === 'active') {
                return $this->safeLoad($bookingPackage, [
                    'package',
                    'sessions.court',
                    'sessions.timeSlot',
                    'bookings',
                    'transactions',
                ]);
            }

            if ($bookingPackage->status !== 'pending_payment') {
                throw new RuntimeException('Chỉ có thể kích hoạt gói đang chờ thanh toán.');
            }

            if ($bookingPackage->sessions->isEmpty()) {
                throw new RuntimeException('Gói chưa có cấu hình buổi cố định.');
            }

            if ($bookingPackage->bookings()->exists()) {
                $bookingPackage->update([
                    'status' => 'active',
                    'paid_at' => now(),
                ]);

                $this->markPackageTransactionSuccess($bookingPackage, $transactionStatus);

                return $this->safeLoad($bookingPackage, [
                    'package',
                    'sessions.court',
                    'sessions.timeSlot',
                    'bookings',
                    'transactions',
                ]);
            }

            foreach ($bookingPackage->sessions as $session) {
                $court = $session->court;
                $timeSlot = $session->timeSlot;
                $sessionSlots = $session->slots->isNotEmpty()
                    ? $session->slots->sortBy('slot_order')->values()
                    : collect([(object) ['timeSlot' => $timeSlot, 'price' => $session->price_per_session]]);
                $firstSlot = $sessionSlots->first()?->timeSlot;
                $lastSlot = $sessionSlots->last()?->timeSlot;

                if (! $court || ! $firstSlot || ! $lastSlot) {
                    throw new RuntimeException('Buổi trong gói thiếu sân hoặc khung giờ.');
                }

                $dates = $this->generateDatesForWeekday(
                    $bookingPackage->start_date,
                    (int) $session->weekday,
                    $bookingPackage->package->type,
                    (int) $bookingPackage->package->duration
                );

                $startTime = $firstSlot->start_time;
                $endTime = $lastSlot->end_time;
                $pricePerSession = (int) ($session->price_per_session ?: $sessionSlots->sum('price'));

                $conflicts = $this->conflictingBookingsForRange($court, $startTime, $endTime, $dates);

                if ($conflicts->isNotEmpty()) {
                    $first = $conflicts->first();
                    $dateText = Carbon::parse($first->slot_date)->format('d/m/Y');

                    throw new RuntimeException("Không thể kích hoạt gói. {$court->name} ngày {$dateText} đã có booking.");
                }

                foreach ($dates as $date) {
                    $booking = Booking::create($this->onlyExistingColumns('bookings', [
                        'booking_package_id' => $bookingPackage->id,
                        'court_id' => $court->id,
                        'time_slot_id' => $firstSlot->id,
                        'user_id' => $bookingPackage->user_id,
                        'slot_date' => $date->toDateString(),
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'total_price' => $pricePerSession,
                        'status' => 'confirmed',
                        'payment_status' => 'paid',
                        'payment_method' => 'package',
                        'note' => "Booking sinh từ gói đã thanh toán #{$bookingPackage->id}",
                    ]));

                    $this->writeBookingLog(
                        $booking->id,
                        $changedBy ?: $bookingPackage->user_id,
                        'none',
                        'confirmed',
                        "Tạo booking sau khi thanh toán gói #{$bookingPackage->id}."
                    );
                }
            }

            $bookingPackage->update([
                'status' => 'active',
                'paid_at' => now(),
            ]);

            $this->markPackageTransactionSuccess($bookingPackage, $transactionStatus);

            return $this->safeLoad($bookingPackage, [
                'package',
                'sessions.court',
                'sessions.timeSlot',
                'bookings',
                'transactions',
            ]);
        });
    }

    /**
     * Hủy gói.
     *
     * pending_payment: hủy giao dịch chờ thanh toán.
     * active/paused: hủy booking tương lai.
     */
    public function cancelPackage(BookingPackage $bookingPackage, string $mode = 'future'): BookingPackage
    {
        return DB::transaction(function () use ($bookingPackage, $mode) {
            $bookingPackage = BookingPackage::query()
                ->lockForUpdate()
                ->findOrFail($bookingPackage->id);

            $now = now('Asia/Ho_Chi_Minh');

            $bookingPackage->bookings()
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->when($mode === 'future', function ($query) use ($now) {
                    $query->where(function ($inner) use ($now) {
                        $inner->where('slot_date', '>', $now->toDateString())
                            ->orWhere(function ($sameDay) use ($now) {
                                $sameDay->where('slot_date', $now->toDateString())
                                    ->where('start_time', '>', $now->format('H:i:s'));
                            });
                    });
                })
                ->update(['status' => 'cancelled']);

            $bookingPackage->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            if (Schema::hasTable('transactions') && Schema::hasColumn('transactions', 'booking_package_id')) {
                DB::table('transactions')
                    ->where('booking_package_id', $bookingPackage->id)
                    ->where('payment_status', 'pending')
                    ->update(['payment_status' => 'failed']);
            }

            return $bookingPackage->refresh();
        });
    }

    public function pausePackage(BookingPackage $bookingPackage): BookingPackage
    {
        return DB::transaction(function () use ($bookingPackage) {
            $bookingPackage = BookingPackage::query()
                ->lockForUpdate()
                ->findOrFail($bookingPackage->id);

            if ($bookingPackage->status !== 'active') {
                throw new RuntimeException('Chỉ có thể tạm dừng gói đang hoạt động.');
            }

            $bookingPackage->update([
                'status' => 'paused',
                'paused_at' => now(),
            ]);

            return $bookingPackage->refresh();
        });
    }

    public function resumePackage(BookingPackage $bookingPackage): BookingPackage
    {
        return DB::transaction(function () use ($bookingPackage) {
            $bookingPackage = BookingPackage::query()
                ->lockForUpdate()
                ->findOrFail($bookingPackage->id);

            if ($bookingPackage->status !== 'paused') {
                throw new RuntimeException('Chỉ có thể kích hoạt lại gói đang tạm dừng.');
            }

            $bookingPackage->update([
                'status' => 'active',
                'paused_at' => null,
            ]);

            return $bookingPackage->refresh();
        });
    }

    private function buildMultiSlotSessionPlans(
        VenuePackage $package,
        array $sessions,
        Carbon|string $startDate,
        bool $lock = false
    ): Collection {
        return collect($sessions)
            ->values()
            ->map(function (array $session, int $index) use ($package, $startDate, $lock) {
                if (! isset($session['time_slot_ids']) && isset($session['time_slot_id'])) {
                    $session['time_slot_ids'] = [$session['time_slot_id']];
                }

                foreach (['court_id', 'time_slot_ids', 'weekday'] as $field) {
                    if (! array_key_exists($field, $session)) {
                        throw new RuntimeException("Thiếu dữ liệu {$field} cho buổi " . ($index + 1) . '.');
                    }
                }

                $weekday = (int) $session['weekday'];

                if ($weekday < 0 || $weekday > 6) {
                    throw new RuntimeException('Thứ trong tuần không hợp lệ.');
                }

                $courtQuery = Court::query()->with('venue');
                $timeSlotQuery = TimeSlot::query();

                if ($lock) {
                    $courtQuery->lockForUpdate();
                    $timeSlotQuery->lockForUpdate();
                }

                $court = $courtQuery->findOrFail($session['court_id']);
                $slotIds = collect($session['time_slot_ids'])
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values();

                if ($slotIds->isEmpty()) {
                    throw new RuntimeException('Vui lòng chọn ít nhất 1 khung giờ cho buổi ' . ($index + 1) . '.');
                }

                $timeSlots = $timeSlotQuery
                    ->whereIn('id', $slotIds)
                    ->orderBy('start_time')
                    ->get();

                if ($timeSlots->count() !== $slotIds->count()) {
                    throw new RuntimeException('Danh sách khung giờ của buổi ' . ($index + 1) . ' không hợp lệ.');
                }

                foreach ($timeSlots as $timeSlot) {
                    $this->assertCanUseSession($package, $court, $timeSlot);
                }

                for ($slotIndex = 1; $slotIndex < $timeSlots->count(); $slotIndex++) {
                    $previous = $timeSlots[$slotIndex - 1];
                    $current = $timeSlots[$slotIndex];

                    if (substr((string) $previous->end_time, 0, 5) !== substr((string) $current->start_time, 0, 5)) {
                        throw new RuntimeException('Các khung giờ trong cùng một buổi phải liền kề nhau.');
                    }
                }

                $dates = $this->generateDatesForWeekday(
                    $startDate,
                    $weekday,
                    $package->type,
                    (int) $package->duration
                );

                if ($dates->isEmpty()) {
                    throw new RuntimeException('Buổi ' . ($index + 1) . ' không sinh được ngày hợp lệ.');
                }

                $firstTimeSlot = $timeSlots->first();
                $lastTimeSlot = $timeSlots->last();
                $startTime = $firstTimeSlot->start_time;
                $endTime = $lastTimeSlot->end_time;

                $conflicts = $this->conflictingBookingsForRange($court, $startTime, $endTime, $dates);

                if ($conflicts->isNotEmpty()) {
                    $first = $conflicts->first();
                    $dateText = Carbon::parse($first->slot_date)->format('d/m/Y');

                    throw new RuntimeException("Không thể tạo gói. {$court->name} ngày {$dateText} đã có booking.");
                }

                $pricedSlots = $timeSlots
                    ->map(fn (TimeSlot $slot) => [
                        'model' => $slot,
                        'price' => $this->priceForDate($slot, $dates->first()),
                    ])
                    ->values();

                return [
                    'court' => $court,
                    'time_slot' => $firstTimeSlot,
                    'first_time_slot' => $firstTimeSlot,
                    'time_slots' => $pricedSlots,
                    'weekday' => $weekday,
                    'dates' => $dates,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'price_per_session' => $pricedSlots->sum('price'),
                ];
            });
    }

    private function buildSessionPlans(
        VenuePackage $package,
        array $sessions,
        Carbon|string $startDate,
        bool $lock = false
    ): Collection {
        return collect($sessions)
            ->values()
            ->map(function (array $session, int $index) use ($package, $startDate, $lock) {
                foreach (['court_id', 'time_slot_id', 'weekday'] as $field) {
                    if (! array_key_exists($field, $session)) {
                        throw new RuntimeException("Thiếu dữ liệu {$field} cho buổi " . ($index + 1) . '.');
                    }
                }

                $weekday = (int) $session['weekday'];

                if ($weekday < 0 || $weekday > 6) {
                    throw new RuntimeException('Thứ trong tuần không hợp lệ.');
                }

                $courtQuery = Court::query()->with('venue');
                $timeSlotQuery = TimeSlot::query();

                if ($lock) {
                    $courtQuery->lockForUpdate();
                    $timeSlotQuery->lockForUpdate();
                }

                $court = $courtQuery->findOrFail($session['court_id']);
                $timeSlot = $timeSlotQuery->findOrFail($session['time_slot_id']);

                $this->assertCanUseSession($package, $court, $timeSlot);

                $dates = $this->generateDatesForWeekday(
                    $startDate,
                    $weekday,
                    $package->type,
                    (int) $package->duration
                );

                if ($dates->isEmpty()) {
                    throw new RuntimeException("Buổi " . ($index + 1) . ' không sinh được ngày hợp lệ.');
                }

                $conflicts = $this->conflictingBookings($court, $timeSlot, $dates);

                if ($conflicts->isNotEmpty()) {
                    $first = $conflicts->first();
                    $dateText = Carbon::parse($first->slot_date)->format('d/m/Y');

                    throw new RuntimeException("Không thể tạo gói. {$court->name} ngày {$dateText} đã có booking.");
                }

                return [
                    'court' => $court,
                    'time_slot' => $timeSlot,
                    'weekday' => $weekday,
                    'dates' => $dates,
                    'price_per_session' => $this->priceForDate($timeSlot, $dates->first()),
                ];
            });
    }

    private function assertPackageIsUsable(VenuePackage $package): void
    {
        if ($package->status !== 'active') {
            throw new RuntimeException('Gói đặt sân hiện không hoạt động.');
        }

        if (! $package->venue?->allow_package_booking) {
            throw new RuntimeException('Cơ sở sân chưa bật chức năng đặt theo gói.');
        }
    }

    private function assertPackageHasCapacity(VenuePackage $package): void
    {
        if (! $package->max_subscribers) {
            return;
        }

        $activeCount = BookingPackage::query()
            ->where('package_id', $package->id)
            ->whereIn('status', ['pending_payment', 'active', 'paused'])
            ->lockForUpdate()
            ->count();

        if ($activeCount >= $package->max_subscribers) {
            throw new RuntimeException('Gói này đã hết số lượng đăng ký.');
        }
    }

    private function assertSessionCountIsValid(VenuePackage $package, array $sessions): void
    {
        $count = count($sessions);
        $max = (int) ($package->max_sessions_per_week ?: 7);

        if ($count < 1) {
            throw new RuntimeException('Vui lòng chọn ít nhất 1 buổi mỗi tuần.');
        }

        if ($count > $max) {
            throw new RuntimeException("Gói này chỉ cho phép tối đa {$max} buổi/tuần.");
        }

        $exactKeys = collect($sessions)->map(function (array $session) {
            $slotIds = collect($session['time_slot_ids'] ?? [$session['time_slot_id'] ?? null])
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->sort()
                ->values()
                ->implode(',');

            return implode('-', [
                $session['weekday'] ?? '',
                $session['court_id'] ?? '',
                $slotIds,
            ]);
        });

        if ($exactKeys->unique()->count() !== $exactKeys->count()) {
            throw new RuntimeException('Không được chọn trùng cùng thứ, sân và khung giờ trong một gói.');
        }

        if ($count === 7) {
            $weekdayCount = collect($sessions)
                ->pluck('weekday')
                ->map(fn ($weekday) => (int) $weekday)
                ->unique()
                ->count();

            if ($weekdayCount !== 7) {
                throw new RuntimeException('Nếu chọn 7 buổi/tuần thì cần chọn đủ 7 ngày khác nhau.');
            }
        }
    }

    private function assertCanUseSession(VenuePackage $package, Court $court, TimeSlot $timeSlot): void
    {
        if ((int) $package->venue_id !== (int) $court->venue_id) {
            throw new RuntimeException('Sân không thuộc cơ sở của gói đã chọn.');
        }

        if ((int) $timeSlot->court_id !== (int) $court->id) {
            throw new RuntimeException('Khung giờ không thuộc sân đã chọn.');
        }

        if (! $court->canBeBooked()) {
            throw new RuntimeException('Sân hiện không cho phép đặt online.');
        }
    }

    private function periodEndDate(Carbon|string $startDate, string $type, int $duration): Carbon
    {
        $start = Carbon::parse($startDate)->startOfDay();

        if ($type === 'week') {
            return $start->copy()->addWeeks($duration)->subDay();
        }

        return $start->copy()->addMonthsNoOverflow($duration)->subDay();
    }

    private function createPendingTransaction(BookingPackage $bookingPackage, int $userId): void
    {
        if (! Schema::hasTable('transactions')) {
            return;
        }

        Transaction::create($this->onlyExistingColumns('transactions', [
            'booking_id' => null,
            'booking_package_id' => $bookingPackage->id,
            'user_id' => $userId,
            'transaction_code' => 'PKG-' . $bookingPackage->id . '-' . now()->format('YmdHis'),
            'amount' => $bookingPackage->final_amount,
            'payment_method' => 'PACKAGE',
            'payment_gateway' => 'internal',
            'payment_status' => 'pending',
            'transaction_time' => now(),
            'note' => "Tạo giao dịch chờ thanh toán cho gói đặt sân #{$bookingPackage->id}.",
        ]));
    }

    private function markPackageTransactionSuccess(BookingPackage $bookingPackage, string $transactionStatus): void
    {
        if (! Schema::hasTable('transactions')) {
            return;
        }

        if (! Schema::hasColumn('transactions', 'booking_package_id')) {
            return;
        }

        $data = $this->onlyExistingColumns('transactions', [
            'payment_status' => $transactionStatus,
            'transaction_time' => now(),
        ]);

        DB::table('transactions')
            ->where('booking_package_id', $bookingPackage->id)
            ->where('payment_status', 'pending')
            ->update($data);
    }

    private function writeBookingLog(
        int $bookingId,
        int $changedBy,
        string $oldStatus,
        string $newStatus,
        string $note
    ): void {
        if (! Schema::hasTable('booking_logs')) {
            return;
        }

        DB::table('booking_logs')->insert($this->onlyExistingColumns('booking_logs', [
            'booking_id' => $bookingId,
            'changed_by' => $changedBy,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'note' => $note,
            'created_at' => now(),
            'updated_at' => now(),
        ]));
    }

    private function priceForDate(TimeSlot $timeSlot, Carbon $date): int
    {
        $slotPrice = SlotPrice::query()
            ->where('time_slot_id', $timeSlot->id)
            ->where('day_of_week', $date->dayOfWeek)
            ->first();

        if ($slotPrice) {
            return (int) $slotPrice->price;
        }

        $start = Carbon::parse($timeSlot->start_time);
        $end = Carbon::parse($timeSlot->end_time);
        $hours = max(0.5, $start->floatDiffInHours($end));

        return (int) round($hours * 150000);
    }

    private function onlyExistingColumns(string $table, array $data): array
    {
        static $columns = [];

        if (! isset($columns[$table])) {
            $columns[$table] = Schema::hasTable($table)
                ? Schema::getColumnListing($table)
                : [];
        }

        return array_intersect_key($data, array_flip($columns[$table]));
    }

    private function safeLoad(BookingPackage $bookingPackage, array $relations): BookingPackage
    {
        try {
            return $bookingPackage->load($relations);
        } catch (Throwable $exception) {
            return $bookingPackage->refresh();
        }
    }
}
