<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>{{ $contract->title ?? 'HỢP ĐỒNG HỢP TÁC KINH DOANH' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #111; line-height: 1.5; font-size: 13px; }
        .page { padding: 18px; }
        h1, h2, h3 { margin: 0 0 8px; }
        .center { text-align: center; }
        .bold { font-weight: 700; }
        .small { font-size: 12px; }
        .section { margin-top: 16px; }
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 12px; }
        p { margin: 4px 0; }
    </style>
</head>
<body>
<div class="page">
    @include('admin.contracts.partials.body')
</div>
</body>
</html>
