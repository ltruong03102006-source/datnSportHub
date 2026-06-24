<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReviewReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking)
    {
    }

    public function build(): self
    {
        return $this->subject('Chia se danh gia ve tran dau cua ban')
            ->view('emails.review_reminder')
            ->with([
                'booking' => $this->booking,
                'court' => $this->booking->court,
                'venue' => $this->booking->court?->venue,
                'user' => $this->booking->user,
            ]);
    }
}
