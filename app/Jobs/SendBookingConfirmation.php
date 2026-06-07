<?php

namespace App\Jobs;

use App\Mail\BookingConfirmationMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendBookingConfirmation implements ShouldQueue
{
    use Queueable, SerializesModels, InteractsWithQueue;

    public $booking;

    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    public function handle()
    {
        try {
            Mail::to($this->booking->user->email)->send(new BookingConfirmationMail($this->booking));
            Log::info("Gửi email booking #{$this->booking->id}");
        } catch (\Throwable $exception) {
            Log::error('Lỗi gửi email booking: ' . $exception->getMessage(), [
                'booking_id' => $this->booking->id,
            ]);
            throw $exception;
        }
    }
}
