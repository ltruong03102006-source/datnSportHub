<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Enums\TransactionType;
use App\Models\Booking;
use App\Models\BookingLog;
use App\Models\PlatformWalletTransaction;
use App\Models\Venue;
use App\Models\WalletTransaction;
use App\Services\BookingCompletionService;
use App\Services\PlatformWalletService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OwnerBookingCalendarController extends Controller
{
    public function index(BookingCompletionService $completionService): View
    {
        $completionService->completeExpiredBookings(ownerId: Auth::id());

        // TỰ ĐỘNG CHUYỂN TRẠNG THÁI "ĐÃ HOÀN THÀNH" CHO CÁC CA ĐÃ QUA
        $now = now('Asia/Ho_Chi_Minh');
        
        // 1. Lấy tất cả các ca đang "Đã xác nhận" của Chủ sân
        $confirmedBookings = Booking::where('status', 'confirmed')
            ->whereHas('court.venue', function ($query) {
                $query->where('owner_id', Auth::id());
            })
            ->get();

        // 2. Nhóm các ca lại thành từng "Đơn hàng" (cùng user, sân, ngày, thời điểm tạo)
        $groupedBookings = $confirmedBookings->groupBy(function($item) {
            return $item->user_id . '_' . $item->court_id . '_' . $item->slot_date->format('Y-m-d') . '_' . $item->created_at;
        });

        // 3. Kiểm tra: Chỉ ghi nhận Hoàn thành khi CA CUỐI CÙNG đã kết thúc
        $idsToComplete = [];
        foreach ($groupedBookings as $group) {
            $maxEndTime = $group->max('end_time');
            $slotDate = $group->first()->slot_date->format('Y-m-d');
            $maxEndDateTime = \Carbon\Carbon::parse($slotDate . ' ' . $maxEndTime, 'Asia/Ho_Chi_Minh');

            if ($now->greaterThanOrEqualTo($maxEndDateTime)) {
                $idsToComplete = array_merge($idsToComplete, $group->pluck('id')->toArray());
            }
        }

        // 4. Cập nhật 1 lần vào Database
        if (!empty($idsToComplete)) {
            Booking::whereIn('id', $idsToComplete)->update(['status' => 'completed']);
        }

        $completionService->settleCompletedBookings(ownerId: Auth::id());

        $venues = Venue::query()
            ->where('owner_id', Auth::id())
            ->with(['courts' => fn ($query) => $query->orderBy('name')])
            ->orderBy('name')
            ->get(['id', 'name']);

        $ownerBookings = Booking::query()
            ->whereHas('court.venue', fn ($query) => $query->where('owner_id', Auth::id()));

        $todayBookings = (clone $ownerBookings)
            ->whereDate('slot_date', today())
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->count();

        $pendingBookings = (clone $ownerBookings)
            ->where('status', 'pending')
            ->count();

        $weekBookings = (clone $ownerBookings)
            ->whereBetween('slot_date', [today()->startOfWeek(), today()->endOfWeek()])
            ->whereNotIn('status', ['cancelled', 'rejected'])
            ->count();

        $confirmedBookings = (clone $ownerBookings)
            ->where('status', 'confirmed')
            ->count();

        $totalCourts = $venues->sum(fn (Venue $venue) => $venue->courts->count());

        return view('owner.bookings.calendar', compact(
            'venues',
            'todayBookings',
            'pendingBookings',
            'weekBookings',
            'confirmedBookings',
            'totalCourts'
        ));
    }

    public function events(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
            'venue_id' => ['nullable', 'integer'],
            'court_id' => ['nullable', 'integer'],
            'status' => ['nullable', 'in:pending,confirmed,completed,cancelled,rejected'],
        ]);

        $start = Carbon::parse($validated['start'])->startOfDay();
        $end = Carbon::parse($validated['end'])->startOfDay();

        $bookings = Booking::query()
            ->whereHas('court.venue', fn ($query) => $query->where('owner_id', $request->user()->id))
            ->when(
                $validated['venue_id'] ?? null,
                fn ($query, $venueId) => $query->whereHas(
                    'court',
                    fn ($court) => $court->where('venue_id', $venueId)
                )
            )
            ->when(
                $validated['court_id'] ?? null,
                fn ($query, $courtId) => $query->where('court_id', $courtId)
            )
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($bQuery) use ($start, $end) {
                    $bQuery->whereDate('slot_date', '>=', $start->toDateString())
                        ->whereDate('slot_date', '<', $end->toDateString());
                })
                ->orWhereHas('items', function ($itemQuery) use ($start, $end) {
                    $itemQuery->whereDate('slot_date', '>=', $start->toDateString())
                        ->whereDate('slot_date', '<', $end->toDateString());
                });
            })
            ->with(['court.venue', 'user', 'services', 'items'])
            ->get();

        $events = collect();

        foreach ($bookings as $booking) {
            $items = $booking->items;

            if ($items->isNotEmpty()) {
                $mappedItems = $items->map(function ($item) use ($booking) {
                    if (in_array($booking->status, ['cancelled', 'rejected'], true)) {
                        $effectiveStatus = $booking->status;
                    } elseif ($item->status === 'cancelled') {
                        $effectiveStatus = 'cancelled';
                    } else {
                        $effectiveStatus = $booking->status;
                    }

                    return [
                        'item' => $item,
                        'effective_status' => $effectiveStatus,
                        'slot_date' => $item->slot_date instanceof Carbon ? $item->slot_date->format('Y-m-d') : Carbon::parse($item->slot_date)->format('Y-m-d'),
                        'start_time' => date('H:i:s', strtotime($item->start_time)),
                        'end_time' => date('H:i:s', strtotime($item->end_time)),
                    ];
                });

                if (!empty($validated['status'])) {
                    $mappedItems = $mappedItems->where('effective_status', $validated['status']);
                } else {
                    $mappedItems = $mappedItems->whereNotIn('effective_status', ['cancelled', 'rejected']);
                }

                $grouped = $mappedItems->groupBy(fn ($row) => $row['slot_date'] . '_' . $row['effective_status']);

                foreach ($grouped as $groupKey => $dateItems) {
                    $first = $dateItems->first();
                    $dateStr = $first['slot_date'];
                    $effectiveStatus = $first['effective_status'];

                    $dateCarbon = Carbon::parse($dateStr);
                    if ($dateCarbon->lt($start) || $dateCarbon->gte($end)) {
                        continue;
                    }

                    $sortedItems = $dateItems->sortBy('start_time')->values();
                    $blocks = [];
                    $currentBlock = null;

                    foreach ($sortedItems as $row) {
                        $itemStart = $row['start_time'];
                        $itemEnd = $row['end_time'];

                        if ($currentBlock === null) {
                            $currentBlock = [
                                'slot_date' => $dateStr,
                                'start_time' => $itemStart,
                                'end_time' => $itemEnd,
                                'effective_status' => $effectiveStatus,
                            ];
                        } else {
                            if ($itemStart === $currentBlock['end_time']) {
                                $currentBlock['end_time'] = $itemEnd;
                            } else {
                                $blocks[] = $currentBlock;
                                $currentBlock = [
                                    'slot_date' => $dateStr,
                                    'start_time' => $itemStart,
                                    'end_time' => $itemEnd,
                                    'effective_status' => $effectiveStatus,
                                ];
                            }
                        }
                    }

                    if ($currentBlock !== null) {
                        $blocks[] = $currentBlock;
                    }

                    foreach ($blocks as $blockIndex => $block) {
                        $events->push($this->formatEvent($booking, $block, $blockIndex, $block['effective_status']));
                    }
                }
            } else {
                $effectiveStatus = $booking->status;
                if (!empty($validated['status'])) {
                    if ($effectiveStatus !== $validated['status']) {
                        continue;
                    }
                } else {
                    if (in_array($effectiveStatus, ['cancelled', 'rejected'], true)) {
                        continue;
                    }
                }

                $dateStr = $booking->slot_date instanceof Carbon ? $booking->slot_date->format('Y-m-d') : Carbon::parse($booking->slot_date)->format('Y-m-d');
                $dateCarbon = Carbon::parse($dateStr);

                if ($dateCarbon->gte($start) && $dateCarbon->lt($end)) {
                    $block = [
                        'slot_date' => $dateStr,
                        'start_time' => date('H:i:s', strtotime($booking->start_time)),
                        'end_time' => date('H:i:s', strtotime($booking->end_time)),
                        'effective_status' => $effectiveStatus,
                    ];
                    $events->push($this->formatEvent($booking, $block, 0, $effectiveStatus));
                }
            }
        }

        return response()->json($events->values());
    }

    public function updateStatus(Request $request, Booking $booking): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,rejected'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $booking = DB::transaction(function () use ($booking, $request, $validated) {
            $lockedBooking = Booking::query()
                ->whereKey($booking->id)
                ->whereHas('court.venue', fn ($query) => $query->where('owner_id', $request->user()->id))
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBooking->status !== 'pending') {
                abort(409, 'Chỉ có thể xử lý booking đang chờ xác nhận.');
            }

            if ($validated['status'] === 'confirmed') {
                $hasConfirmedConflict = Booking::query()
                    ->where('court_id', $lockedBooking->court_id)
                    ->whereDate('slot_date', $lockedBooking->slot_date)
                    ->where('status', 'confirmed')
                    ->whereKeyNot($lockedBooking->id)
                    ->where(function ($query) use ($lockedBooking) {
                        $query->where('start_time', '<', $lockedBooking->end_time)
                            ->where('end_time', '>', $lockedBooking->start_time);
                    })
                    ->lockForUpdate()
                    ->exists();

                if ($hasConfirmedConflict) {
                    abort(409, 'Ca này đã có booking được xác nhận trước đó. Vui lòng từ chối yêu cầu này hoặc chọn ca khác.');
                }
            }

            $oldStatus = $lockedBooking->status;
            
            $updateData = ['status' => $validated['status']];
            if ($validated['status'] === 'rejected') {
                if ($lockedBooking->isPaid()) {
                    $updateData['refund_amount'] = $lockedBooking->total_price;
                    $updateData['refund_status'] = 'refunded';

                    $user = $lockedBooking->user;
                    $wallet = $user->getOrCreateWallet();
                    $balanceBefore = $wallet->balance;

                    $wallet->balance += $lockedBooking->total_price;
                    $wallet->save();

                    WalletTransaction::create([
                        'wallet_id' => $wallet->id,
                        'booking_id' => $lockedBooking->id,
                        'reference' => 'REFUND-B' . $lockedBooking->id . '-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(5)),
                        'type' => TransactionType::REFUND,
                        'amount' => $lockedBooking->total_price,
                        'balance_before' => $balanceBefore,
                        'balance_after' => $wallet->balance,
                        'description' => 'Hoàn tiền do chủ sân từ chối đơn đặt #' . $lockedBooking->id,
                    ]);

                    if ($this->isPlatformOnlinePayment($lockedBooking) && class_exists(PlatformWalletService::class)) {
                        app(PlatformWalletService::class)->debit(
                            amount: $lockedBooking->total_price,
                            type: PlatformWalletTransaction::TYPE_CUSTOMER_REFUND_OUT,
                            description: 'Hoàn tiền do chủ sân từ chối đơn #' . $lockedBooking->id,
                            referenceType: 'booking',
                            referenceId: $lockedBooking->id,
                            reference: 'REFUND-' . $lockedBooking->id,
                            performedBy: $request->user()->id
                        );
                    }
                } else {
                    $updateData['refund_amount'] = 0;
                    $updateData['refund_status'] = 'none';
                }
            }
            $lockedBooking->update($updateData);

            BookingLog::create([
                'booking_id' => $lockedBooking->id,
                'changed_by' => $request->user()->id,
                'old_status' => $oldStatus,
                'new_status' => $validated['status'],
                'note' => $validated['note']
                    ?? ($validated['status'] === 'confirmed'
                        ? 'Chủ sân xác nhận booking từ lịch quản lý.'
                        : 'Chủ sân từ chối booking từ lịch quản lý.'),
            ]);

            return $lockedBooking->load(['court.venue', 'user']);
        });

        return response()->json([
            'message' => $validated['status'] === 'confirmed'
                ? 'Đã xác nhận booking.'
                : 'Đã từ chối booking.',
            'event' => $this->formatEvent($booking),
            'pending_count' => Booking::query()
                ->whereHas('court.venue', fn ($query) => $query->where('owner_id', $request->user()->id))
                ->where('status', 'pending')
                ->count(),
        ]);

        // Notify customer
        try {
            if ($validated['status'] === 'confirmed') {
                app(\App\Services\NotificationService::class)->notifyBookingConfirmed($booking);
            } else {
                app(\App\Services\NotificationService::class)->notifyBookingRejected($booking);
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }

    public function cancel(Request $request, Booking $booking): JsonResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ], [
            'reason.required' => 'Vui lòng nhập lý do hủy.',
            'reason.max' => 'Lý do hủy tối đa 1000 ký tự.',
        ]);

        $booking = DB::transaction(function () use ($booking, $request, $validated) {
            $lockedBooking = Booking::query()
                ->whereKey($booking->id)
                ->whereHas('court.venue', fn ($query) => $query->where('owner_id', $request->user()->id))
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedBooking->status !== 'confirmed') {
                abort(409, 'Chỉ có thể hủy booking đã được xác nhận.');
            }

            $oldStatus = $lockedBooking->status;
            
            // CẬP NHẬT: Thêm tiền tố và lưu logic hoàn tiền (Chủ sân hủy -> Khách không mất phí, hoàn 100% ca đó nếu đã thanh toán)
            $updateData = [
                'status' => 'cancelled',
                'cancel_reason' => 'Chủ sân hủy: ' . $validated['reason'],
                'cancellation_fee' => 0, 
            ];

            if ($lockedBooking->isPaid()) {
                $updateData['refund_amount'] = $lockedBooking->total_price;
                $updateData['refund_status'] = 'refunded';

                $user = $lockedBooking->user;
                $wallet = $user->getOrCreateWallet();
                $balanceBefore = $wallet->balance;

                $wallet->balance += $lockedBooking->total_price;
                $wallet->save();

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'booking_id' => $lockedBooking->id,
                    'reference' => 'REFUND-B' . $lockedBooking->id . '-' . \Illuminate\Support\Str::upper(\Illuminate\Support\Str::random(5)),
                    'type' => TransactionType::REFUND,
                    'amount' => $lockedBooking->total_price,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $wallet->balance,
                    'description' => 'Hoàn tiền do chủ sân hủy đơn đặt #' . $lockedBooking->id,
                ]);

                if ($this->isPlatformOnlinePayment($lockedBooking) && class_exists(PlatformWalletService::class)) {
                    app(PlatformWalletService::class)->debit(
                        amount: $lockedBooking->total_price,
                        type: PlatformWalletTransaction::TYPE_CUSTOMER_REFUND_OUT,
                        description: 'Hoàn tiền do chủ sân hủy đơn #' . $lockedBooking->id,
                        referenceType: 'booking',
                        referenceId: $lockedBooking->id,
                        reference: 'REFUND-' . $lockedBooking->id,
                        performedBy: $request->user()->id
                    );
                }
            } else {
                $updateData['refund_amount'] = 0;
                $updateData['refund_status'] = 'none';
            }

            $lockedBooking->update($updateData);

            BookingLog::create([
                'booking_id' => $lockedBooking->id,
                'changed_by' => $request->user()->id,
                'old_status' => $oldStatus,
                'new_status' => 'cancelled',
                'note' => 'Chủ sân chủ động hủy. Lý do: ' . $validated['reason'],
            ]);

            return $lockedBooking->load(['court.venue', 'user']);
        });

        return response()->json([
            'message' => 'Đã hủy ca sân và ghi nhận hoàn tiền 100% cho khách!',
            'event' => $this->formatEvent($booking),
        ]);

        // Notify customer about cancellation
        try {
            app(\App\Services\NotificationService::class)->notifyBookingCancelled($booking);
        } catch (\Throwable $e) {
            // ignore
        }
    }

    private function isPlatformOnlinePayment(Booking $booking): bool
    {
        return in_array(strtolower((string) $booking->payment_method), [
            'vnpay',
            'online',
            'bank_transfer',
            'platform_transfer',
            'wallet',
        ], true);
    }

    private function formatEvent(Booking $booking, ?array $block = null, int $blockIndex = 0): array
    {
        $status = $this->statusMeta($booking->status);
        $date = $block['slot_date'] ?? ($booking->slot_date instanceof Carbon ? $booking->slot_date->format('Y-m-d') : Carbon::parse($booking->slot_date)->format('Y-m-d'));
        $startTime = $block['start_time'] ?? date('H:i:s', strtotime($booking->start_time));
        $endTime = $block['end_time'] ?? date('H:i:s', strtotime($booking->end_time));

        // --- LOGIC MỚI: GHI ĐÈ HIỂN THỊ (VISUAL OVERRIDE) ---
        $now = now('Asia/Ho_Chi_Minh');
        $endDateTime = \Carbon\Carbon::parse($date . ' ' . $endTime, 'Asia/Ho_Chi_Minh');
        $isPast = $now->greaterThanOrEqualTo($endDateTime);

        // Nếu DB đang là "Đã xác nhận" nhưng giờ đã qua -> Khoác áo "Đã hoàn thành"
        if ($booking->status === 'confirmed' && $isPast) {
            $status = ['label' => 'Đã đá xong', 'color' => '#2563eb']; // Đổi màu xanh dương
        }

        // Trạng thái giả lập gửi xuống Frontend để giấu nút "Hủy sân"
        $displayStatus = ($booking->status === 'confirmed' && $isPast) ? 'completed' : $booking->status;
        // --- KẾT THÚC LOGIC ---

        $timeLabel = substr($startTime, 0, 5).' - '.substr($endTime, 0, 5);
        $dateLabel = Carbon::parse($date)->format('d/m/Y');

        $eventId = (string) $booking->id;
        if ($block !== null) {
            $eventId .= '_' . $date . '_' . str_replace(':', '', $startTime);
        }

        return [
            'id' => $eventId,
            'title' => $booking->court->name.' - '.$booking->user->name,
            'start' => $date.'T'.$startTime,
            'end' => $date.'T'.$endTime,
            'backgroundColor' => $status['color'],
            'borderColor' => $status['color'],
            'textColor' => '#ffffff',
            'extendedProps' => [
                'booking_id' => $booking->id,
                'venue_name' => $booking->court->venue->name,
                'court_name' => $booking->court->name,
                'customer_name' => $booking->user->name,
                'customer_email' => $booking->user->email,
                'customer_phone' => $booking->user->phone ?? 'Chưa cập nhật SĐT', 

                'status' => $displayStatus, 
                'status_label' => $status['label'], 

                'total_price' => number_format((float) $booking->total_price, 0, ',', '.').' đ',
                'note' => $booking->note,
                'cancel_reason' => $booking->cancel_reason,
                'date_label' => $dateLabel,
                'time_label' => $timeLabel,

                // BỔ SUNG DÒNG NÀY ĐỂ TRUYỀN DATA DỊCH VỤ SANG JAVASCRIPT
                'services' => $booking->services, 
            ],
        ];
    }

    private function statusMeta(string $status): array
    {
        return match ($status) {
            'confirmed' => ['label' => 'Đã xác nhận', 'color' => '#047857'],
            'completed' => ['label' => 'Đã hoàn thành', 'color' => '#2563eb'],
            'cancelled' => ['label' => 'Đã hủy', 'color' => '#64748b'],
            'rejected' => ['label' => 'Đã từ chối', 'color' => '#dc2626'],
            default => ['label' => 'Chờ xác nhận', 'color' => '#d97706'],
        };
    }
}
