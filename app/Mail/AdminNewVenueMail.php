<?php

namespace App\Mail;

use App\Models\Venue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminNewVenueMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Venue $venue,
        public string $adminUrl
    ) {
    }

    public function build(): self
    {
        return $this->subject("Thông báo: Cơ sở mới \"{$this->venue->name}\" cần duyệt")
            ->view('emails.admin_new_venue');
    }
}
