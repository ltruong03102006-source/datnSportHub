<?php

namespace App\Notifications;

use App\Models\VenueTransferRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VenueTransferContractNotification extends Notification
{
    use Queueable;

    public function __construct(public VenueTransferRequest $transfer) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => '📄 Bạn nhận được Hợp đồng chuyển nhượng cơ sở',
            'message' => sprintf(
                'Chủ sân %s vừa gửi Hợp đồng chuyển nhượng cơ sở "%s" (HDCN-#%d) đến bạn. Vui lòng kiểm tra và xác nhận.',
                $this->transfer->fromOwner?->name ?? 'Bên chuyển nhượng',
                $this->transfer->venue?->name ?? 'Cơ sở',
                $this->transfer->id
            ),
            'url' => route('owner.web.venues.transfers.accept', $this->transfer->id),
            'transfer_id' => $this->transfer->id,
        ];
    }
}
