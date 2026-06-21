<?php

namespace App\Mail;

use App\Models\OwnerRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OwnerPasswordSetupMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public OwnerRegistration $registration,
        public string $setupUrl,
    ) {
    }

    public function build(): self
    {
        return $this->subject('Thiết lập mật khẩu tài khoản chủ sân')
            ->view('emails.owner-password-setup');
    }
}
