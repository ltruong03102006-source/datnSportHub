<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$booking = App\Models\Booking::find(144);
$slotDate = $booking->slot_date instanceof Carbon\Carbon ? $booking->slot_date->format('Y-m-d') : Carbon\Carbon::parse($booking->slot_date)->format('Y-m-d');
$startTime = substr((string) $booking->start_time, 0, 8);
if (strlen($startTime) === 5) {
    $startTime .= ':00';
}
$startsAt = Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $slotDate.' '.$startTime, 'Asia/Ho_Chi_Minh');
$now = Carbon\Carbon::now('Asia/Ho_Chi_Minh');
echo json_encode([
    'startsAt' => $startsAt->toDateTimeString(),
    'now' => $now->toDateTimeString(),
    'diff' => $now->diffInHours($startsAt, false)
]);
