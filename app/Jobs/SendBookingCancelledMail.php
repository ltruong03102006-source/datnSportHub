<?php

namespace App\Jobs;

use App\Mail\BookingCancelledMail;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBookingCancelledMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function handle(): void
    {
        if (! $this->booking->relationLoaded('user')) {
            $this->booking->load('user', 'court');
        }

        if (! empty($this->booking->user?->email)) {
            Mail::to($this->booking->user->email)->send(new BookingCancelledMail($this->booking));
        }
    }
}
