<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Xác nhận đặt sân</title>
</head>
<body>
    <h1>Booking #{{ $booking->id }} đã được tạo</h1>
    <p>Xin chào {{ $user->name ?? 'Khách hàng' }},</p>
    <p>Đơn đặt sân của bạn đã được ghi nhận với thông tin:</p>
    <ul>
        <li>Sân: {{ $court->name ?? 'Không xác định' }}</li>
        <li>Ngày: {{ $booking->slot_date->format('d/m/Y') }}</li>
        <li>Giờ: {{ $booking->start_time }} - {{ $booking->end_time }}</li>
        <li>Giá: {{ number_format($booking->total_price, 0, ',', '.') }} VND</li>
        <li>Trạng thái: {{ ucfirst($booking->status) }}</li>
    </ul>
    <p>Chúng tôi sẽ liên hệ lại khi chủ sân xác nhận.</p>
    <p>Xin cảm ơn.</p>
</body>
</html>
