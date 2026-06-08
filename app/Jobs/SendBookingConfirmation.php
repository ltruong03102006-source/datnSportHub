<?php

namespace App\Jobs;

use App\Mail\BookingConfirmationMail;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBookingConfirmation implements ShouldQueue
{
    use Queueable, SerializesModels, InteractsWithQueue;

    public int $tries = 3;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->afterCommit();
    }

    public function handle(): void
    {
        $this->booking->loadMissing(['user', 'court.venue']);

        if (! $this->booking->user?->email) {
            Log::warning('Khong gui email xac nhan dat san vi nguoi dung chua co email.', [
                'booking_id' => $this->booking->id,
                'user_id' => $this->booking->user_id,
            ]);

            return;
        }

        Mail::to($this->booking->user->email)->send(new BookingConfirmationMail($this->booking));

        Log::info('Da gui email xac nhan dat san.', [
            'booking_id' => $this->booking->id,
            'email' => $this->booking->user->email,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Gui email xac nhan dat san that bai.', [
            'booking_id' => $this->booking->id,
            'message' => $exception->getMessage(),
        ]);
    }
}
