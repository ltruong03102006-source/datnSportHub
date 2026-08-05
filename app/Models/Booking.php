<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    /** Trạng thái được tính vào thống kê doanh thu/lượt đặt */
    public const VALID_STATUSES = ['confirmed', 'completed'];

    /** Trạng thái thanh toán được coi là đã thu tiền */
    public const PAID_STATUSES = ['paid', 'completed', 'success'];

    /** Hình thức thanh toán coi như đã thu tiền (trả tại sân, gói) */
    public const PAID_METHODS = ['cod', 'cash', 'offline', 'package'];

    // ĐÂY LÀ CHUẨN CỦA LARAVEL
    protected $fillable = [
        'court_id',
        'booking_package_id',
        'time_slot_id',
        'user_id',
        'slot_date',
        'start_time',
        'end_time',
        'total_price',
        'status',
        'payment_method',
        'payment_status',
        'vnpay_tran_id',
        'paid_at',
        'review_reminder_sent_at',
        'checked_in_at',
        'checked_in_by',
        'checkin_note',
        'no_show_at',
        'no_show_by',
        'note',
        'cancel_reason',
        'cancellation_fee',
         'refund_amount', 
         'refund_status',
         'platform_fee',
        'owner_earnings',
        'commission_rate',
        'settlement_status',
        'settled_at',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'slot_date' => 'date',
        'paid_at' => 'datetime',
        'review_reminder_sent_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'no_show_at' => 'datetime',
        'platform_fee' => 'decimal:0',
        'owner_earnings' => 'decimal:0',
        'commission_rate' => 'decimal:2',
        'settlement_status' => \App\Enums\SettlementStatus::class,
        'settled_at' => 'datetime',
    ];

    public function court(): BelongsTo
    {
        return $this->belongsTo(Court::class);
    }

    /**
     * Scope: Lấy lịch đặt của các sân con chỉ định (dùng cho thống kê)
     */
    public function scopeForCourts(Builder $query, $courtIds): Builder
    {
        return $query->whereIn('court_id', $courtIds);
    }

    /**
     * Scope: Lọc lịch đặt trong khoảng ngày chơi
     */
    public function scopeInPeriod(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('slot_date', [
            $startDate instanceof \DateTimeInterface ? $startDate->format('Y-m-d') : $startDate,
            $endDate instanceof \DateTimeInterface ? $endDate->format('Y-m-d') : $endDate,
        ]);
    }

    /**
     * Lịch đặt này đã được coi là thu tiền chưa
     */
    public function isPaid(): bool
    {
        if (in_array((string) $this->payment_status, self::PAID_STATUSES, true)) {
            return true;
        }

        return in_array(strtolower((string) $this->payment_method), self::PAID_METHODS, true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    public function noShowBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'no_show_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function timeSlot(): BelongsTo { return $this->belongsTo(TimeSlot::class); }
    public function bookingPackage(): BelongsTo { return $this->belongsTo(BookingPackage::class); }
    public function items(): HasMany { return $this->hasMany(BookingItem::class); }
    public function rescheduleRequests(): HasMany { return $this->hasMany(BookingRescheduleRequest::class); }
    
    public function vouchers(): BelongsToMany
    {
        return $this->belongsToMany(Voucher::class, 'booking_vouchers')
            ->withPivot('discount_amount')
            ->withTimestamps();
    }

    public function recordStatusChange(int $changedBy, string $oldStatus, string $newStatus, ?string $note = null, $createdAt = null): BookingLog
    {
        $log = new BookingLog();
        $log->booking_id = $this->id;
        $log->changed_by = $changedBy;
        $log->old_status = $oldStatus;
        $log->new_status = $newStatus;
        $log->note = $note;
        $log->timestamps = false;

        if ($createdAt !== null) {
            $log->created_at = $createdAt;
        }

        $log->save();

        return $log;
    }
    public function getCancellationPolicy(): array
    {
        $slotDate = $this->slot_date instanceof \Carbon\Carbon 
            ? $this->slot_date->format('Y-m-d') 
            : \Carbon\Carbon::parse($this->slot_date)->format('Y-m-d');
            
        $startsAt = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $slotDate . ' ' . $this->start_time, 'Asia/Ho_Chi_Minh');
        $now = \Carbon\Carbon::now('Asia/Ho_Chi_Minh');
        
        // Tính số giờ còn lại trước khi đá (false để giữ số âm nếu đã quá giờ)
        $hoursDiff = $now->diffInHours($startsAt, false);

        if ($hoursDiff >= 24) {
            return ['fee_percent' => 0, 'refund_percent' => 100, 'hours' => $hoursDiff];
        } elseif ($hoursDiff >= 12) {
            return ['fee_percent' => 50, 'refund_percent' => 50, 'hours' => $hoursDiff];
        } else {
            return ['fee_percent' => 100, 'refund_percent' => 0, 'hours' => $hoursDiff];
        }
    }
    // Bổ sung: Một đơn đặt sân có thể bao gồm nhiều dịch vụ mua kèm
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'booking_services')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }
    // --- BẮT ĐẦU: LOGIC TỰ ĐỘNG HOÀN KHO KHI HỦY ĐƠN ---
    protected static function booted()
    {
        static::updated(function ($booking) {
            // FIX QUAN TRỌNG: Dùng wasChanged() thay vì isDirty() trong sự kiện updated
            if ($booking->wasChanged('status') && in_array($booking->status, ['cancelled', 'rejected'])) {
                
                // Lấy các dịch vụ có trong đơn này
                $services = $booking->services;
                
                if ($services->count() > 0) {
                    foreach ($services as $service) {
                        // Nếu mặt hàng này có quản lý tồn kho (stock !== null)
                        if ($service->stock !== null) {
                            // CỘNG TRẢ LẠI SỐ LƯỢNG VÀO KHO
                            $service->increment('stock', $service->pivot->quantity);
                        }
                    }
                }
            }
        });
    }
    // --- KẾT THÚC: LOGIC TỰ ĐỘNG HOÀN KHO ---
    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function getCommissionAmountAttribute(): float
    {
        return (float) ($this->platform_fee ?? 0);
    }

    public function getOwnerAmountAttribute(): float
    {
        return (float) ($this->owner_earnings ?? 0);
    }
}
