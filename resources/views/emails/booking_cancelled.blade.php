<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Thông báo hủy đặt sân</title>
</head>
<body>
    <h2>Đặt sân của bạn đã bị hủy</h2>
    <p>Xin chào {{ $user->name ?? 'quý khách' }},</p>
    <p>Đơn đặt sân của bạn tại {{ $court->name ?? 'sân' }} đã được chủ sân hủy.</p>
    <p>Ngày: {{ $booking->slot_date }}</p>
    <p>Thời gian: {{ $booking->start_time }} - {{ $booking->end_time }}</p>
    <p>Cảm ơn bạn đã sử dụng dịch vụ.</p>
</body>
</html>
