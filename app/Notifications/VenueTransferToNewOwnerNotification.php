<?php

namespace App\Notifications;

use App\Models\VenueTransferRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VenueTransferToNewOwnerNotification extends Notification
{
    use Queueable;

    public $transfer;

    public function __construct(VenueTransferRequest $transfer)
    {
        $this->transfer = $transfer;
    }

    // Chỉ định gửi qua database (lưu vào bảng notifications để hiện lên quả chuông)
    public function via($notifiable): array
    {
        return ['database'];
    }

    // Dữ liệu sẽ được lưu vào database để frontend render
    public function toDatabase($notifiable): array
    {
        return [
            'title' => '🎉 Nhận cơ sở mới',
            'message' => 'Bạn đã được chuyển quyền sở hữu cơ sở: ' . $this->transfer->venue->name,
            'url' => route('owner.web.venues.index'), // Link ấn vào thông báo sẽ chuyển đến đâu
            'type' => 'success'
        ];
    }
}