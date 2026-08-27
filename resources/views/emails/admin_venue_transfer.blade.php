<p>Xin chào Admin,</p>

<p>Hệ thống vừa nhận được một yêu cầu chuyển nhượng cơ sở thể thao đã được hai bên chủ sân hoàn tất ký kết và đang chờ bạn phê duyệt.</p>

<p><strong>Thông tin chi tiết yêu cầu chuyển nhượng:</strong></p>
<ul>
    <li><strong>Mã yêu cầu:</strong> #{{ $transfer->id }}</li>
    <li><strong>Cơ sở chuyển nhượng:</strong> {{ $transfer->venue->name ?? 'N/A' }}</li>
    <li><strong>Bên chuyển nhượng (Chủ cũ):</strong> {{ $transfer->fromOwner->name ?? 'N/A' }} ({{ $transfer->fromOwner->email ?? 'N/A' }})</li>
    <li><strong>Bên nhận chuyển nhượng (Chủ mới):</strong> {{ $transfer->toOwner->name ?? 'N/A' }} ({{ $transfer->toOwner->email ?? 'N/A' }})</li>
    @if(!empty($transfer->contract_date))
    <li><strong>Ngày lập hợp đồng:</strong> {{ \Illuminate\Support\Carbon::parse($transfer->contract_date)->format('d/m/Y') }}</li>
    @endif
    <li><strong>Thời gian nộp yêu cầu:</strong> {{ $transfer->updated_at ? $transfer->updated_at->format('d/m/Y H:i:s') : now()->format('d/m/Y H:i:s') }}</li>
</ul>

<p>Vui lòng truy cập trang quản trị để xem chi tiết hợp đồng và tiến hành phê duyệt:</p>
<p><a href="{{ $adminUrl }}" style="display: inline-block; padding: 10px 20px; background-color: #10b981; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">Xem và Phê duyệt Chuyển nhượng</a></p>

<p>Trân trọng,<br>Hệ thống DantSportHub</p>
