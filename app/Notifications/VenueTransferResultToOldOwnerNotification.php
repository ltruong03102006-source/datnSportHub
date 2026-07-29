<?php

namespace App\Notifications;

use App\Models\VenueTransferRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VenueTransferResultToOldOwnerNotification extends Notification
{
    use Queueable;

    public $transfer;

    public function __construct(VenueTransferRequest $transfer)
    {
        $this->transfer = $transfer;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $isApproved = $this->transfer->status === 'approved';
        
        return [
            'title' => $isApproved ? '✅ Chuyển nhượng thành công' : '❌ Chuyển nhượng bị từ chối',
            'message' => $isApproved
                ? "Cơ sở {$this->transfer->venue->name} đã đổi chủ thành công."
                : "Yêu cầu chuyển nhượng cơ sở {$this->transfer->venue->name} đã bị từ chối. Lý do: {$this->transfer->admin_note}",
            'url' => route('owner.web.venues.index'),
            'type' => $isApproved ? 'success' : 'error'
        ];
    }
}