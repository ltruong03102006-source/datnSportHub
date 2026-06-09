<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý điểm sân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4 py-lg-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Quản lý sân</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Quản lý điểm sân</h1>
            <p class="text-muted mb-0">Tạo và quản lý các điểm sân trước khi thêm sân nhỏ.</p>
        </div>
        <a href="{{ route('owner.web.venues.create') }}" class="btn btn-primary">+ Thêm điểm sân</a>
    </div>

    @if (request('created') == '1' || session('success'))
        <div class="alert alert-success" role="alert">
            Đã tạo điểm sân thành công.
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <p class="text-muted mb-0">Danh sách điểm sân sẽ hiển thị ở đây sau khi bạn tạo nội dung đầu tiên.</p>
        </div>
    </div>
</div>
</body>
</html>
