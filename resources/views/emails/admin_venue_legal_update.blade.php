<p>Xin chào Admin,</p>

<p>Hệ thống vừa nhận được yêu cầu cập nhật hồ sơ pháp lý mới từ cơ sở thể thao.</p>

<p><strong>Thông tin chi tiết:</strong></p>
<ul>
    <li><strong>Tên cơ sở:</strong> {{ $venue->name }}</li>
    <li><strong>Chủ sân:</strong> {{ $ownerName }} ({{ $venue->owner->email ?? 'N/A' }})</li>
    <li><strong>Địa chỉ:</strong> {{ $venue->address }}</li>
    <li><strong>Thời gian gửi yêu cầu:</strong> {{ now()->format('d/m/Y H:i:s') }}</li>
</ul>

<p>Vui lòng truy cập trang quản trị để kiểm tra và phê duyệt hồ sơ pháp lý mới:</p>
<p><a href="{{ $adminUrl }}" style="display: inline-block; padding: 10px 20px; background-color: #1e40af; color: #ffffff; text-decoration: none; border-radius: 6px;">Xem và duyệt hồ sơ pháp lý</a></p>

<p>Cảm ơn bạn!</p>
