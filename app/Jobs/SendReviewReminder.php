<?php

namespace App\Jobs;

use App\Mail\ReviewReminderMail;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendReviewReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $bookingId)
    {
        $this->afterCommit();
    }

    public function handle(): void
    {
        $booking = Booking::with(['user', 'court.venue'])->find($this->bookingId);

        if (! $booking || $booking->status !== 'completed' || ! $booking->user?->email) {
            return;
        }

        if (Review::where('booking_id', $booking->id)->exists()) {
            return;
        }

        Mail::to($booking->user->email)->send(new ReviewReminderMail($booking));

        Log::info('Da gui email nhac danh gia booking.', [
            'booking_id' => $booking->id,
            'user_id' => $booking->user_id,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Gui email nhac danh gia that bai.', [
            'booking_id' => $this->bookingId,
            'message' => $exception->getMessage(),
        ]);
    }
}
