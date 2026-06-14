<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CompleteExpiredBookings extends Command
{
    protected $signature = 'bookings:complete-expired';

    protected $description = 'Mark confirmed bookings as completed once their slot end time has passed';

    public function handle(): int
    {
        $now = Carbon::now();

        $bookings = Booking::where('status', 'confirmed')
            ->get();

        $updated = 0;

        foreach ($bookings as $booking) {
            $slotEnd = Carbon::parse($booking->slot_date->format('Y-m-d') . ' ' . $booking->end_time);

            if ($slotEnd->gt($now)) {
                continue;
            }

            DB::transaction(function () use ($booking, $now): void {
                $lockedBooking = Booking::where('id', $booking->id)->lockForUpdate()->first();

                if (! $lockedBooking || $lockedBooking->status !== 'confirmed') {
                    return;
                }

                $oldStatus = $lockedBooking->status;
                $lockedBooking->status = 'completed';
                $lockedBooking->save();
                $lockedBooking->recordStatusChange(0, $oldStatus, 'completed', 'Scheduler completed expired booking', $now);
            });

            $updated++;
        }

        $this->info("Completed {$updated} booking(s).");

        return self::SUCCESS;
    }
}
