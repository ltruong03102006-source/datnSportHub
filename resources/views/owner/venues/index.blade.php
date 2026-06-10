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
        <div class="d-flex gap-2">
            <a href="{{ route('owner.web.calendar.index') }}" class="btn btn-outline-success">Lịch đặt sân</a>
            <a href="{{ route('owner.web.venues.create') }}" class="btn btn-primary">+ Thêm điểm sân</a>
        </div>
    </div>

   @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif (request('created') == '1')
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Đã tạo điểm sân thành công.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @elseif (request('updated') == '1')
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 8px; font-weight: 500;">
            Đã cập nhật thông tin điểm sân thành công.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 d-flex align-items-center" role="alert" style="border-radius: 8px; font-weight: 500;">
            <svg style="width: 24px; height: 24px;" class="me-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-4 d-flex align-items-center" role="alert" style="border-radius: 8px; font-weight: 500;">
            <svg style="width: 24px; height: 24px;" class="me-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(isset($venues) && $venues->isEmpty())
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <p class="text-muted mb-0">Bạn chưa có điểm sân nào. Hãy bấm "Thêm điểm sân" để bắt đầu.</p>
            </div>
        </div>
    @elseif(isset($venues))
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            @foreach($venues as $venue)
                <div class="col">
                    <div class="card shadow-sm border-0 h-100">
                        @if($venue->banner)
                            <img src="{{ asset('storage/' . $venue->banner) }}" class="card-img-top" alt="{{ $venue->name }}" style="height: 200px; object-fit: cover;">
                        @else
                            <div class="card-img-top bg-secondary bg-opacity-10 d-flex align-items-center justify-content-center text-muted" style="height: 200px;">
                                Không có ảnh
                            </div>
                        @endif
                        
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0 fw-bold">{{ $venue->name }}</h5>
                                
                                @if($venue->status === 'active')
                                    <span class="badge bg-success">Hoạt động</span>
                                @elseif($venue->status === 'pending')
                                    <span class="badge bg-warning text-dark">Chờ duyệt</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($venue->status) }}</span>
                                @endif
                            </div>
                            
                            <p class="card-text text-muted small mb-3">
                                📍 {{ \Illuminate\Support\Str::limit($venue->address, 65) }}
                            </p>
                        </div>

                        <div class="card-footer bg-white border-top-0 pt-0 pb-3">
                            <div class="d-flex gap-2">
                                <a href="{{ route('owner.web.venues.show', $venue->id) }}" class="btn btn-primary btn-sm flex-fill">
                                    Chi tiết & Sân con
                                </a>
                                
                                <a href="{{ route('owner.web.venues.edit', $venue->id) }}" class="btn btn-outline-secondary btn-sm">
                                    Sửa
                                </a>

                                @if($venue->status === 'active')
                                 <form action="{{ route('owner.web.venues.destroy', $venue->id) }}" method="POST" onsubmit="return confirm('Tạm ngừng hoạt động sân này? Khách hàng sẽ không thể đặt sân.');">
                                     @csrf
                                     @method('DELETE')
                                     <button type="submit" class="btn btn-outline-danger btn-sm" title="Tạm ngừng hoạt động">
                                         Tạm ngừng
                                     </button>
                                 </form>
                             @elseif($venue->status === 'inactive')
                                 <form action="{{ route('owner.web.venues.restore', $venue->id) }}" method="POST" onsubmit="return confirm('Xác nhận mở lại sân này?');">
                                     @csrf
                                     @method('PATCH')
                                     <button type="submit" class="btn btn-success btn-sm text-white" title="Mở lại sân">
                                         Mở lại
                                     </button>
                                 </form>
                             @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-danger" role="alert">
            Chưa truyền biến $venues từ Controller sang View.
        </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
