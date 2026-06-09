<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        return $this->subject('Xac nhan dat san')
            ->view('emails.booking_confirmation')
            ->with([
                'booking' => $this->booking,
                'court' => $this->booking->court,
                'user' => $this->booking->user,
            ]);
    }
}
