<?php

namespace App\Listeners;

use App\Events\BookingCompleted;
use App\Services\SettlementService;
use App\Enums\SettlementStatus;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessBookingSettlement implements ShouldQueue // Chạy ngầm (Queue) để không làm chậm web
{
    public function __construct(protected SettlementService $settlementService)
    {}

    public function handle(BookingCompleted $event): void
    {
        $booking = $event->booking;
        
        \Log::channel('settlement')->info("Bắt đầu đối soát Booking #{$booking->id}");

        try {
            $this->settlementService->process($booking);
            \Log::channel('settlement')->info("✅ Đối soát thành công Booking #{$booking->id}");
            
        } catch (\Exception $e) {
            // Đánh dấu lỗi vào Database để Admin biết
            $booking->update(['settlement_status' => \App\Enums\SettlementStatus::FAILED]);
            
            // Ghi Log lỗi chi tiết
            \Log::channel('settlement')->error("❌ Lỗi đối soát Booking #{$booking->id}: " . $e->getMessage());
            
            // Ném lỗi ra lại để Queue xử lý (thử lại sau)
            throw $e;
        }
    }
}