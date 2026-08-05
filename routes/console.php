<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule; // Nhớ có dòng này
use App\Models\MatchPost; // Nhớ có dòng này
use App\Services\ContractLifecycleService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('contracts:sync-statuses', function () {
    $result = app(ContractLifecycleService::class)->syncStatuses();

    $this->info("Expired {$result['expired_contracts']} contract(s).");
    $this->info("Activated {$result['activated_venues']} venue(s).");
})->purpose('Sync contract expiration and scheduled venue activation.');

// --- CODE AUTO-EXPIRED CHẠY NGẦM CỦA BẠN Ở ĐÂY ---
Schedule::call(function () {
    MatchPost::where('status', 'open')
        ->where(function($query) {
            // Tình huống 1: Ngày đá đã là quá khứ (Hôm qua)
            $query->where('play_date', '<', now()->toDateString())
                  // Tình huống 2: Ngày đá là hôm nay, nhưng giờ đá đã qua
                  ->orWhere(function($sq) {
                      $sq->where('play_date', now()->toDateString())
                         ->where('play_time', '<', now()->format('H:i'));
                  });
        })->update(['status' => 'expired']);
})->everyMinute();
// --- AUTO EXPIRE BOOKINGS QUÁ HẠN THANH TOÁN ---
Schedule::call(function () {
    $holdTimeMinutes = \App\Models\Setting::get('booking_hold_time', 15);
    $expiredBookings = \App\Models\Booking::with('vouchers')
        ->where('status', 'pending')
        ->where('payment_status', 'unpaid')
        ->where('created_at', '<=', now()->subMinutes($holdTimeMinutes))
        ->get();
        
    foreach ($expiredBookings as $booking) {
        $booking->update([
            'status' => 'cancelled',
            'cancel_reason' => 'Hệ thống tự động hủy do quá hạn thanh toán.',
        ]);

        // HOÀN VOUCHER
        $voucherNote = '';
        if ($booking->vouchers->isNotEmpty()) {
            foreach ($booking->vouchers as $voucher) {
                if ($voucher->used_count > 0) {
                    $voucher->decrement('used_count');
                }
                $voucherNote .= " Hoàn voucher {$voucher->code}.";
            }
        }

        \App\Models\BookingLog::create([
            'booking_id' => $booking->id,
            'changed_by' => null, // Hệ thống
            'old_status' => 'pending',
            'new_status' => 'cancelled',
            'note' => "Quá hạn thanh toán ({$holdTimeMinutes} phút). Slot đã được giải phóng." . $voucherNote,
        ]);
    }
})->everyMinute();
Schedule::command('contracts:sync-statuses')->hourly();

// --- AUTO EXPIRE VOUCHERS QUÁ HẠN SỬ DỤNG ---
Schedule::call(function () {
    \App\Models\Voucher::where('status', 'active')
        ->whereNotNull('end_date')
        ->where('end_date', '<', now())
        ->update(['status' => 'expired']);
})->everyMinute();
// --------------------------------------------------
