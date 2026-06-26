<?php
namespace App\Notifications;
use App\Models\BookingRescheduleRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
class BookingRescheduleNotification extends Notification { use Queueable; public function __construct(private BookingRescheduleRequest $request, private string $message) {} public function via(object $notifiable): array { return ['database']; } public function toArray(object $notifiable): array { return ['request_id'=>$this->request->id,'booking_id'=>$this->request->booking_id,'status'=>$this->request->status,'message'=>$this->message]; } }
