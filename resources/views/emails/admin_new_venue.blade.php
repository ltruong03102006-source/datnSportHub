<p>Xin chào Admin,</p>

<p>Hệ thống vừa nhận được thông báo tạo cơ sở thể thao mới kèm hồ sơ pháp lý cần bạn duyệt.</p>

<p><strong>Thông tin cơ sở:</strong></p>
<ul>
    <li><strong>Tên cơ sở:</strong> {{ $venue->name }}</li>
    <li><strong>Chủ sân:</strong> {{ $venue->owner->name ?? 'N/A' }} ({{ $venue->owner->email ?? 'N/A' }})</li>
    <li><strong>Địa chỉ:</strong> {{ $venue->address }}</li>
    <li><strong>Thời gian tạo:</strong> {{ $venue->created_at ? $venue->created_at->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s') }}</li>
</ul>

<p>Vui lòng truy cập trang quản trị để kiểm tra hồ sơ và phê duyệt cơ sở:</p>
<p><a href="{{ $adminUrl }}" style="display: inline-block; padding: 10px 20px; background-color: #1e40af; color: #ffffff; text-decoration: none; border-radius: 6px;">Xem và duyệt cơ sở</a></p>

<p>Cảm ơn bạn!</p>
