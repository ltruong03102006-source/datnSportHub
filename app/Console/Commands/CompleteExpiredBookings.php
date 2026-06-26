<?php

namespace App\Console\Commands;

use App\Services\BookingCompletionService;
use Illuminate\Console\Command;

class CompleteExpiredBookings extends Command
{
    protected $signature = 'bookings:complete-expired';

    protected $description = 'Mark confirmed bookings as completed once their slot end time has passed';

    public function handle(BookingCompletionService $completionService): int
    {
        $updated = $completionService->completeExpiredBookings();

        $this->info("Completed {$updated} booking(s).");

        return self::SUCCESS;
    }
}
