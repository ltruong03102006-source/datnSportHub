<?php

namespace App\Mail;

use App\Models\VenueTransferRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminVenueTransferMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public VenueTransferRequest $transfer,
        public string $adminUrl
    ) {
    }

    public function build(): self
    {
        $venueName = $this->transfer->venue->name ?? 'Cơ sở';

        return $this->subject("Thông báo: Yêu cầu chuyển nhượng cơ sở \"{$venueName}\" cần duyệt")
            ->view('emails.admin_venue_transfer');
    }
}
