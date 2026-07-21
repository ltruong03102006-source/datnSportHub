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
        
        // Gọi hàm chia tiền
        $this->settlementService->process($booking);
    }
}