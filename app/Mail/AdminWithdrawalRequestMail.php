<?php

namespace App\Mail;

use App\Models\WithdrawalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminWithdrawalRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public WithdrawalRequest $withdrawalRequest,
        public string $adminUrl
    ) {
    }

    public function build(): self
    {
        $requester = $this->withdrawalRequest->owner;
        $requesterName = $requester->name ?? 'Người dùng';
        $roleText = ($requester && $requester->role === 'owner') ? 'Chủ sân' : 'Khách hàng';
        $formattedAmount = number_format((float) $this->withdrawalRequest->amount, 0, ',', '.') . 'đ';

        return $this->subject("Thông báo: Yêu cầu rút tiền {$formattedAmount} từ {$roleText} \"{$requesterName}\" cần phê duyệt")
            ->view('emails.admin_withdrawal_request', [
                'withdrawal' => $this->withdrawalRequest,
                'requester' => $requester,
                'roleText' => $roleText,
                'adminUrl' => $this->adminUrl,
            ]);
    }
}
