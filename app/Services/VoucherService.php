<?php

namespace App\Services;

use App\Models\Voucher;
use App\Models\Venue;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VoucherService
{
    /**
     * Create a new voucher
     *
     * @param array $data
     * @return Voucher
     * @throws Exception
     */
    public function create(array $data): Voucher
    {
        // Tự động sinh mã nếu không có
        if (empty($data['code'])) {
            $data['code'] = $this->generateCode();
        } else {
            // Kiểm tra mã unique nếu có truyền vào
            if (Voucher::where('code', $data['code'])->exists()) {
                throw new Exception("Voucher code already exists.");
            }
        }

        // Đảm bảo owner_id hợp lệ
        if (empty($data['owner_id'])) {
            throw new Exception("Owner ID is required.");
        }

        // Validate venues ownership
        $venueIds = $data['venue_ids'] ?? [];
        if (!empty($venueIds)) {
            $this->validateOwnership($data['owner_id'], $venueIds);
        }

        return DB::transaction(function () use ($data, $venueIds) {
            $voucher = Voucher::create($data);
            
            if (!empty($venueIds)) {
                $voucher->venues()->sync($venueIds);
            }
            
            return $voucher;
        });
    }

    /**
     * Generate a unique voucher code
     *
     * @param string $prefix
     * @return string
     */
    public function generateCode(string $prefix = 'FIELD'): string
    {
        do {
            $code = strtoupper($prefix . '-' . Str::random(6));
        } while (Voucher::where('code', $code)->exists());

        return $code;
    }

    /**
     * Validate if the owner actually owns the given sport fields
     *
     * @param int $ownerId
     * @param array|int $sportFieldIds
     * @return bool
     * @throws Exception
     */
    public function validateOwnership(int $ownerId, $venueIds): bool
    {
        $ids = (array) $venueIds;

        $count = Venue::whereIn('id', $ids)
            ->where('owner_id', $ownerId)
            ->count();

        if ($count !== count($ids)) {
            throw new Exception("Owner does not own all the provided venues.");
        }

        return true;
    }

    /**
     * Update an existing voucher for owner with strict business rules
     *
     * @param int $voucherId
     * @param int $ownerId
     * @param array $data
     * @return Voucher
     * @throws Exception
     */
    public function updateForOwner(int $voucherId, int $ownerId, array $data): Voucher
    {
        $voucher = Voucher::where('id', $voucherId)
            ->where('owner_id', $ownerId)
            ->first();

        if (!$voucher) {
            throw new Exception("Voucher không tồn tại hoặc truy cập bị từ chối.");
        }

        $hasBeenUsed = $voucher->used_count > 0 || DB::table('booking_vouchers')->where('voucher_id', $voucherId)->exists();

        // Không cho phép sửa mã voucher
        unset($data['code']);

        // Nếu đã có giao dịch / lượt sử dụng: Không cho phép sửa discount_type, discount_value, venue_ids
        if ($hasBeenUsed) {
            unset($data['discount_type'], $data['discount_value'], $data['applies_to_all_fields'], $data['venue_ids']);
        }

        // Ràng buộc số lượng lượt dùng tối đa không được nhỏ hơn số lượt đã sử dụng
        if (array_key_exists('usage_limit', $data) && !is_null($data['usage_limit'])) {
            if ($data['usage_limit'] < $voucher->used_count) {
                throw new Exception("Số lượng tối đa không được nhỏ hơn số lượt đã sử dụng ({$voucher->used_count}).");
            }
        }

        return DB::transaction(function () use ($voucher, $data, $hasBeenUsed) {
            $voucher->update($data);

            if (!$hasBeenUsed && isset($data['venue_ids'])) {
                $venueIds = (array) $data['venue_ids'];
                if (!empty($venueIds)) {
                    $this->validateOwnership($voucher->owner_id, $venueIds);
                }
                $voucher->venues()->sync($venueIds);
            }

            return $voucher->load('venues');
        });
    }

    /**
     * Update an existing voucher
     *
     * @param int $voucherId
     * @param array $data
     * @return Voucher
     * @throws Exception
     */
    public function update(int $voucherId, array $data): Voucher
    {
        $voucher = Voucher::findOrFail($voucherId);

        if ($voucher->used_count > 0 || DB::table('booking_vouchers')->where('voucher_id', $voucherId)->exists()) {
            throw new Exception("Cannot update voucher. It has already been used.");
        }

        // Validate code uniqueness if it's being updated
        if (isset($data['code']) && $data['code'] !== $voucher->code && Voucher::where('code', $data['code'])->exists()) {
            throw new Exception("Voucher code already exists.");
        }

        return DB::transaction(function () use ($voucher, $data) {
            $voucher->update($data);
            return $voucher;
        });
    }

    /**
     * Delete a voucher (soft delete)
     *
     * @param int $voucherId
     * @return bool
     * @throws Exception
     */
    public function delete(int $voucherId): bool
    {
        $voucher = Voucher::findOrFail($voucherId);

        if ($voucher->used_count > 0 || DB::table('booking_vouchers')->where('voucher_id', $voucherId)->exists()) {
            throw new Exception("Cannot delete voucher. It has already been used.");
        }

        return DB::transaction(function () use ($voucher) {
            return $voucher->delete();
        });
    }

    /**
     * Disable a voucher
     *
     * @param int $voucherId
     * @return Voucher
     */
    public function disable(int $voucherId): Voucher
    {
        $voucher = Voucher::findOrFail($voucherId);
        $voucher->status = 'disabled';
        $voucher->save();

        return $voucher;
    }

    /**
     * Extend voucher end date and reactivate if expired
     *
     * @param int $voucherId
     * @param string $newEndDate
     * @return Voucher
     */
    public function extend(int $voucherId, string $newEndDate): Voucher
    {
        $voucher = Voucher::findOrFail($voucherId);
        $voucher->end_date = $newEndDate;
        
        if ($voucher->status === 'expired' || $voucher->status === 'disabled') {
            $voucher->status = 'active';
        }
        
        $voucher->save();

        return $voucher;
    }

    /**
     * Extend voucher end date and/or add usage limit for owner
     *
     * @param int $voucherId
     * @param int $ownerId
     * @param array $data
     * @return Voucher
     * @throws Exception
     */
    public function extendForOwner(int $voucherId, int $ownerId, array $data): Voucher
    {
        $voucher = Voucher::where('id', $voucherId)
            ->where('owner_id', $ownerId)
            ->first();

        if (!$voucher) {
            throw new Exception("Voucher không tồn tại hoặc truy cập bị từ chối.");
        }

        // Kéo dài thời gian áp dụng
        if (!empty($data['new_end_date'])) {
            $voucher->end_date = $data['new_end_date'];
        } elseif (!empty($data['extend_days'])) {
            $days = (int) $data['extend_days'];
            if ($voucher->end_date && \Illuminate\Support\Carbon::parse($voucher->end_date)->isFuture()) {
                $voucher->end_date = \Illuminate\Support\Carbon::parse($voucher->end_date)->addDays($days);
            } else {
                $voucher->end_date = \Illuminate\Support\Carbon::now()->addDays($days);
            }
        }

        // Tăng thêm số lượng lượt sử dụng
        if (!empty($data['add_quantity'])) {
            $addQty = (int) $data['add_quantity'];
            if (is_null($voucher->usage_limit)) {
                $voucher->usage_limit = $voucher->used_count + $addQty;
            } else {
                $voucher->usage_limit += $addQty;
            }
        } elseif (array_key_exists('new_usage_limit', $data) && !is_null($data['new_usage_limit'])) {
            $newLimit = (int) $data['new_usage_limit'];
            if ($newLimit < $voucher->used_count) {
                throw new Exception("Giới hạn lượt dùng mới phải lớn hơn hoặc bằng số lượt đã dùng ({$voucher->used_count}).");
            }
            $voucher->usage_limit = $newLimit;
        }

        // Tự động kích hoạt lại nếu voucher đang hết hạn hoặc tắt do hết lượt
        if (in_array($voucher->status, ['expired', 'disabled'], true)) {
            $now = \Illuminate\Support\Carbon::now();
            $hasEndDateValid = is_null($voucher->end_date) || $voucher->end_date >= $now;
            $hasUsageValid = is_null($voucher->usage_limit) || $voucher->used_count < $voucher->usage_limit;

            if ($hasEndDateValid && $hasUsageValid) {
                $voucher->status = 'active';
            }
        }

        $voucher->save();

        return $voucher;
    }

    /**
     * Get detailed voucher information with statistics and usage history for owner
     *
     * @param int $voucherId
     * @param int $ownerId
     * @return array
     * @throws Exception
     */
    public function getDetailForOwner(int $voucherId, int $ownerId): array
    {
        $voucher = Voucher::with(['venues', 'bookings.user', 'bookings.court.venue'])
            ->where('id', $voucherId)
            ->where('owner_id', $ownerId)
            ->first();

        if (!$voucher) {
            throw new Exception("Voucher not found or access denied.", 404);
        }

        $usedBookings = $voucher->bookings;

        $usedCount = $voucher->used_count ?? $usedBookings->count();
        $totalDiscount = (float) $usedBookings->sum('pivot.discount_amount');

        $maxRevenue = $usedBookings->isNotEmpty() ? (float) $usedBookings->max('total_price') : 0.00;
        $minRevenue = $usedBookings->isNotEmpty() ? (float) $usedBookings->min('total_price') : 0.00;

        $usageRate = null;
        if (!is_null($voucher->usage_limit) && $voucher->usage_limit > 0) {
            $usageRate = round(($usedCount / $voucher->usage_limit) * 100, 2);
        }

        $bookingList = $usedBookings->map(function ($booking) {
            $discountAmount = (float) ($booking->pivot->discount_amount ?? 0);
            $paidAmount = (float) $booking->total_price;
            $originalAmount = $paidAmount + $discountAmount;

            return [
                'booking_id' => $booking->id,
                'user_name' => $booking->user ? $booking->user->name : 'N/A',
                'user_phone' => $booking->user ? $booking->user->phone : null,
                'user_email' => $booking->user ? $booking->user->email : null,
                'booking_date' => $booking->slot_date ? $booking->slot_date->format('Y-m-d') : ($booking->created_at ? $booking->created_at->format('Y-m-d H:i:s') : null),
                'court_name' => $booking->court ? $booking->court->name : 'N/A',
                'venue_name' => ($booking->court && $booking->court->venue) ? $booking->court->venue->name : 'N/A',
                'original_amount' => $originalAmount,
                'discount_amount' => $discountAmount,
                'actual_paid_amount' => $paidAmount,
            ];
        });

        return [
            'voucher' => $voucher,
            'statistics' => [
                'used_count' => $usedCount,
                'total_discount' => $totalDiscount,
                'max_booking_revenue' => $maxRevenue,
                'min_booking_revenue' => $minRevenue,
                'usage_rate' => $usageRate,
            ],
            'used_bookings' => $bookingList,
        ];
    }

    /**
     * Check if a voucher is eligible for booking
     */
    public function checkEligibility(Voucher $voucher, int $courtId, string $date, array $slots, float $totalPrice, ?int $userId): array
    {
        // 1. Status
        if ($voucher->status !== 'active') {
            return ['eligible' => false, 'discount' => 0, 'reason' => 'Voucher không hoạt động.'];
        }

        // 2. Date Range
        $now = \Illuminate\Support\Carbon::now();
        if ($voucher->start_date && $voucher->start_date->isFuture()) {
            return ['eligible' => false, 'discount' => 0, 'reason' => 'Voucher chưa đến thời gian áp dụng.'];
        }
        if ($voucher->end_date && $voucher->end_date->isPast()) {
            return ['eligible' => false, 'discount' => 0, 'reason' => 'Voucher đã hết hạn sử dụng.'];
        }

        // 3. Usage Limit (global)
        if (!is_null($voucher->usage_limit) && $voucher->used_count >= $voucher->usage_limit) {
            return ['eligible' => false, 'discount' => 0, 'reason' => 'Voucher đã hết lượt sử dụng.'];
        }

        // 3b. Per-user usage limit: nếu có giới hạn lượt dùng cho từng user, kiểm tra xem user đã dùng voucher này bao nhiêu lần
        if (!is_null($voucher->max_uses_per_user) && !is_null($userId)) {
            $userUses = \Illuminate\Support\Facades\DB::table('booking_vouchers')
                ->join('bookings', 'booking_vouchers.booking_id', '=', 'bookings.id')
                ->where('booking_vouchers.voucher_id', $voucher->id)
                ->where('bookings.user_id', $userId)
                ->whereNotIn('bookings.status', ['cancelled', 'rejected'])
                ->count();

            if ($userUses >= $voucher->max_uses_per_user) {
                return ['eligible' => false, 'discount' => 0, 'reason' => 'Bạn đã dùng voucher này hết lượt.'];
            }
        }

        // 4. Venue/Field association
        $court = \App\Models\Court::find($courtId);
        if (!$court) {
            return ['eligible' => false, 'discount' => 0, 'reason' => 'Sân không tồn tại.'];
        }
        $venueId = $court->venue_id;

        if (!$voucher->applies_to_all_fields) {
            $hasVenue = $voucher->venues()->where('venues.id', $venueId)->exists();
            if (!$hasVenue) {
                return ['eligible' => false, 'discount' => 0, 'reason' => 'Voucher không áp dụng cho cơ sở này.'];
            }
        } else {
            // Check if voucher owner is the same as venue owner
            if ($voucher->owner_id !== $court->venue->owner_id) {
                return ['eligible' => false, 'discount' => 0, 'reason' => 'Voucher không thuộc cơ sở này.'];
            }
        }

        // 5. Min Booking Value
        if (!is_null($voucher->min_booking_value) && $totalPrice < $voucher->min_booking_value) {
            return [
                'eligible' => false, 
                'discount' => 0, 
                'reason' => 'Đơn từ ' . number_format($voucher->min_booking_value, 0, ',', '.') . 'đ trở lên.'
            ];
        }

        // 6. Days of Week
        $dayOfWeek = (int) date('w', strtotime($date)); // 0 (CN) -> 6 (Thứ 7)
        if (!empty($voucher->apply_days)) {
            $dayNameMap = [
                'sunday' => 0, 'monday' => 1, 'tuesday' => 2, 'wednesday' => 3,
                'thursday' => 4, 'friday' => 5, 'saturday' => 6
            ];
            $applyDays = array_map(function($d) use ($dayNameMap) {
                $lower = strtolower((string)$d);
                return isset($dayNameMap[$lower]) ? $dayNameMap[$lower] : (int)$d;
            }, (array) $voucher->apply_days);

            if (!in_array($dayOfWeek, $applyDays, true)) {
                $dayNames = [0 => 'Chủ Nhật', 1 => 'Thứ 2', 2 => 'Thứ 3', 3 => 'Thứ 4', 4 => 'Thứ 5', 5 => 'Thứ 6', 6 => 'Thứ 7'];
                $allowedDays = array_map(fn($d) => $dayNames[$d] ?? $d, $applyDays);
                return ['eligible' => false, 'discount' => 0, 'reason' => 'Không áp dụng cho ngày đã chọn (Chỉ áp dụng: ' . implode(', ', $allowedDays) . ').'];
            }
        }

        // 7. Time Slots
        if (!empty($voucher->time_slots)) {
            $voucherTimeSlots = (array) $voucher->time_slots;
            
            $validVSlots = array_filter($voucherTimeSlots, function ($vs) {
                return !empty($vs['start']) && !empty($vs['end']);
            });

            if (!empty($validVSlots)) {
                foreach ($slots as $slot) {
                    $slotStart = substr($slot['start_time'], 0, 5);
                    $slotEnd = substr($slot['end_time'], 0, 5);
                    
                    $slotMatched = false;
                    foreach ($validVSlots as $vSlot) {
                        $vStart = substr($vSlot['start'], 0, 5);
                        $vEnd = substr($vSlot['end'], 0, 5);
                        
                        if ($slotStart >= $vStart && $slotEnd <= $vEnd) {
                            $slotMatched = true;
                            break;
                        }
                    }
                    if (!$slotMatched) {
                        return ['eligible' => false, 'discount' => 0, 'reason' => 'Không áp dụng cho khung giờ đặt sân này.'];
                    }
                }
            }
        }

        // 8. Max uses per user (Default to 1 if not configured)
        if ($userId) {
            $maxUses = !is_null($voucher->max_uses_per_user) ? (int) $voucher->max_uses_per_user : 1;
            
            $userUses = \Illuminate\Support\Facades\DB::table('bookings')
                ->join('booking_vouchers', 'bookings.id', '=', 'booking_vouchers.booking_id')
                ->where('bookings.user_id', $userId)
                ->where('booking_vouchers.voucher_id', $voucher->id)
                ->whereNotIn('bookings.status', ['cancelled', 'rejected'])
                ->count();

            if ($userUses >= $maxUses) {
                return ['eligible' => false, 'discount' => 0, 'reason' => 'Bạn đã dùng mã này tối đa ' . $maxUses . ' lần.'];
            }
        }

        // Calculate discount
        $discount = 0.0;
        if (in_array($voucher->discount_type, ['percent', 'percentage'], true)) {
            $discount = ($totalPrice * (float)$voucher->discount_value) / 100.0;
            if (!is_null($voucher->max_discount_amount) && (float)$voucher->max_discount_amount > 0 && $discount > (float)$voucher->max_discount_amount) {
                $discount = (float)$voucher->max_discount_amount;
            }
        } else {
            $discount = (float)$voucher->discount_value;
        }

        if ($discount > $totalPrice) {
            $discount = $totalPrice;
        }

        return [
            'eligible' => true,
            'discount' => $discount,
            'reason' => null
        ];
    }

    /**
     * Get available vouchers for a court
     */
    public function getAvailableVouchersForCourt(int $courtId, string $date, array $slots, float $totalPrice, ?int $userId): \Illuminate\Support\Collection
    {
        $court = \App\Models\Court::with('venue')->find($courtId);
        if (!$court || !$court->venue) {
            return collect();
        }

        $venueId = $court->venue_id;
        $ownerId = $court->venue->owner_id;

        // Query active vouchers of owner that have remaining usages
        $vouchers = Voucher::where('owner_id', $ownerId)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('usage_limit')
                  ->orWhereRaw('used_count < usage_limit');
            })
            ->where(function ($q) use ($venueId) {
                $q->where('applies_to_all_fields', true)
                  ->orWhereHas('venues', function ($vq) use ($venueId) {
                      $vq->where('venues.id', $venueId);
                  });
            })
            ->get();

        return $vouchers->map(function ($voucher) use ($courtId, $date, $slots, $totalPrice, $userId) {
            $eligibility = $this->checkEligibility($voucher, $courtId, $date, $slots, $totalPrice, $userId);
            
            $voucher->is_applicable = $eligibility['eligible'];
            $voucher->calculated_discount = $eligibility['discount'];
            $voucher->inapplicable_reason = $eligibility['reason'];

            // Flag whether current user has used this voucher in a non-cancelled booking
            $userHasUsed = false;
            if (!is_null($userId)) {
                $userUses = \Illuminate\Support\Facades\DB::table('booking_vouchers')
                    ->join('bookings', 'booking_vouchers.booking_id', '=', 'bookings.id')
                    ->where('booking_vouchers.voucher_id', $voucher->id)
                    ->where('bookings.user_id', $userId)
                    ->whereNotIn('bookings.status', ['cancelled', 'rejected'])
                    ->count();

                $userHasUsed = $userUses > 0;
            }

            $voucher->user_has_used = $userHasUsed;

            return $voucher;
        });
    }

    /**
     * Increment usage of a voucher and notify the owner if it runs out of stock.
     */
    public function incrementUsage(Voucher $voucher): void
    {
        // Tăng số lượt đã dùng (global)
        $voucher->increment('used_count');
        $voucher->refresh();

        // Nếu có giới hạn lượt dùng và đã đạt hoặc vượt giới hạn, tắt voucher (không cho dùng tiếp)
        if (!is_null($voucher->usage_limit) && $voucher->used_count >= $voucher->usage_limit) {
            try {
                // Đặt trạng thái voucher về 'disabled' để không thể dùng tiếp
                $voucher->status = 'disabled';
                $voucher->save();

                $notificationService = app(\App\Services\NotificationService::class);
                $title = "Voucher {$voucher->code} đã hết lượt sử dụng";
                $content = "Mã giảm giá '{$voucher->code}' của bạn đã đạt giới hạn sử dụng ({$voucher->used_count}/{$voucher->usage_limit}). Vui lòng bổ sung thêm lượt sử dụng hoặc gia hạn mã.";
                $link = route('owner.web.vouchers.show', $voucher->id);
                $notificationService->create($voucher->owner_id, $title, $content, $link, 'voucher_out_of_stock');
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send voucher out of stock notification: " . $e->getMessage());
            }
        }
    }
}
