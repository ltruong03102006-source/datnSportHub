<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>HỢP ĐỒNG HỢP TÁC KINH DOANH</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; margin: 0; padding: 0; color: #222; }
        .page { width: 210mm; min-height: 297mm; padding: 20mm; box-sizing: border-box; }
        h1 { font-size: 18px; margin-bottom: 0; }
        .section { margin-top: 20px; }
        .section-title { font-size: 14px; font-weight: bold; margin-bottom: 8px; border-bottom: 1px solid #ddd; padding-bottom: 4px; }
        .row { display: flex; flex-wrap: wrap; margin-bottom: 10px; }
        .label { width: 35%; font-weight: bold; }
        .value { width: 65%; }
        .small { font-size: 12px; color: #555; }
        .bordered { border: 1px solid #ddd; padding: 14px; border-radius: 6px; }
        .notes { white-space: pre-wrap; line-height: 1.5; }
        .heading-block { text-align: center; margin-bottom: 24px; }
        .heading-block h1 { font-size: 20px; text-transform: uppercase; letter-spacing: 0.05em; }
        .two-column { display: flex; flex-wrap: wrap; gap: 12px; }
        .two-column .panel { flex: 1 1 48%; border: 1px solid #ddd; border-radius: 6px; padding: 12px; box-sizing: border-box; }
    </style>
</head>
<body>
    <div class="page">
        <div class="heading-block">
            <h1>HỢP ĐỒNG HỢP TÁC KINH DOANH</h1>
            <div class="small">Ngày xuất PDF: {{ now()->format('Y-m-d H:i') }}</div>
            <div class="small">Mã hợp đồng: {{ $contract->contract_code }}</div>
        </div>

        <div class="section bordered">
            <div class="section-title">THÔNG TIN HỢP ĐỒNG</div>
            <div class="row"><div class="label">Mã hợp đồng</div><div class="value">{{ $contract->contract_code }}</div></div>
            <div class="row"><div class="label">Tiêu đề</div><div class="value">{{ $contract->title }}</div></div>
            <div class="row"><div class="label">Nội dung</div><div class="value notes">{{ $contract->content }}</div></div>
            <div class="row"><div class="label">Hoa hồng</div><div class="value">{{ number_format($contract->commission_rate, 2) }}%</div></div>
            <div class="row"><div class="label">Ngày bắt đầu</div><div class="value">{{ $contract->start_date?->format('Y-m-d') }}</div></div>
            <div class="row"><div class="label">Ngày kết thúc</div><div class="value">{{ $contract->end_date?->format('Y-m-d') }}</div></div>
            <div class="row"><div class="label">Trạng thái</div><div class="value">{{ ucfirst($contract->status) }}</div></div>
        </div>

        <div class="section two-column">
            <div class="panel">
                <div class="section-title">THÔNG TIN CHỦ SÂN</div>
                <div class="row"><div class="label">Họ và tên</div><div class="value">{{ $contract->owner?->name ?? '-' }}</div></div>
                <div class="row"><div class="label">Email</div><div class="value">{{ $contract->owner?->email ?? '-' }}</div></div>
                <div class="row"><div class="label">Số điện thoại</div><div class="value">{{ $contract->owner?->phone ?? '-' }}</div></div>
            </div>
            <div class="panel">
                <div class="section-title">THÔNG TIN ADMIN</div>
                <div class="row"><div class="label">Họ và tên</div><div class="value">{{ $contract->creator?->name ?? '-' }}</div></div>
                <div class="row"><div class="label">Email</div><div class="value">{{ $contract->creator?->email ?? '-' }}</div></div>
            </div>
        </div>

        <div class="section bordered">
            <div class="section-title">THÔNG TIN KHÁC</div>
            <div class="row"><div class="label">Ngày tạo</div><div class="value">{{ $contract->created_at->format('Y-m-d H:i') }}</div></div>
            <div class="row"><div class="label">Ngày ký</div><div class="value">{{ $contract->signed_at?->format('Y-m-d H:i') ?? '-' }}</div></div>
            <div class="row"><div class="label">Ghi chú</div><div class="value notes">{{ $contract->note ?? '-' }}</div></div>
            <div class="row"><div class="label">Lý do từ chối</div><div class="value notes">{{ $contract->rejection_reason ?? '-' }}</div></div>
        </div>
    </div>
</body>
</html>
