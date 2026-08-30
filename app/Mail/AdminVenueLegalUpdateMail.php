<?php

namespace App\Mail;

use App\Models\Venue;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminVenueLegalUpdateMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Venue $venue,
        public string $adminUrl
    ) {
    }

    public function build(): self
    {
        $ownerName = $this->venue->owner->name ?? 'Chủ sân';

        return $this->subject("Thông báo: Cơ sở \"{$this->venue->name}\" vừa cập nhật hồ sơ pháp lý cần phê duyệt")
            ->view('emails.admin_venue_legal_update', [
                'venue' => $this->venue,
                'ownerName' => $ownerName,
                'adminUrl' => $this->adminUrl,
            ]);
    }
}
