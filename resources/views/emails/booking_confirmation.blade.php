<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Xac nhan dat san</title>
</head>
<body>
    <h1>Booking #{{ $booking->id }} da duoc tao</h1>
    <p>Xin chao {{ $user->name ?? 'Khach hang' }},</p>
    <p>Don dat san cua ban da duoc ghi nhan voi thong tin:</p>
    <ul>
        <li>San: {{ $court->name ?? 'Khong xac dinh' }}</li>
        <li>Dia diem: {{ $court->venue->name ?? 'Khong xac dinh' }}</li>
        <li>Ngay: {{ $booking->slot_date->format('d/m/Y') }}</li>
        <li>Gio: {{ $booking->start_time }} - {{ $booking->end_time }}</li>
        <li>Gia: {{ number_format($booking->total_price, 0, ',', '.') }} VND</li>
        <li>Trang thai: {{ ucfirst($booking->status) }}</li>
    </ul>
    <p>Chung toi se lien he lai khi chu san xac nhan.</p>
    <p>Xin cam on.</p>
</body>
</html>
