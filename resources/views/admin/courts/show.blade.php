@extends('admin.layouts.app')

@section('title', 'Chi tiết Sân - ' . $court->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-eye"></i> Chi tiết Sân: {{ $court->name }}
                </h1>
                <a href="{{ route('admin.courts.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>

    <!-- Alerts -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5 class="alert-heading"><i class="fas fa-exclamation-circle"></i> Lỗi</h5>
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Basic Info -->
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin cơ bản</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">ID</label>
                            <p class="h6">{{ $court->id }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Tên sân</label>
                            <p class="h6">{{ $court->name }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Cơ sở sân</label>
                            <p class="h6">
                                <a href="{{ route('admin.venues.index') }}">
                                    {{ $court->venue->name }}
                                </a>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Chủ sân</label>
                            <p class="h6">{{ $court->venue->owner->name }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Thể thao</label>
                            <p class="h6">{{ $court->venue->sport->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Địa chỉ</label>
                            <p class="h6">{{ $court->venue->address }}</p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Trạng thái</label>
                            <p class="h6">
                                @if ($court->status === 'active')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Hoạt động
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        <i class="fas fa-ban"></i> Đã ẩn
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Có thể đặt online?</label>
                            <p class="h6">
                                @if ($court->is_bookable_online)
                                    <span class="badge bg-success">Có</span>
                                @else
                                    <span class="badge bg-secondary">Không</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Ngày tạo</label>
                            <p class="h6">{{ $court->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Cập nhật lần cuối</label>
                            <p class="h6">{{ $court->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h5 class="mb-0"><i class="fas fa-cogs"></i> Hành động</h5>
                </div>
                <div class="card-body d-flex gap-2">
                    <!-- Toggle Status Form -->
                    <form method="POST" action="{{ route('admin.courts.toggle-status', $court) }}" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button 
                            type="submit" 
                            class="btn {{ $court->status === 'active' ? 'btn-warning' : 'btn-success' }}"
                            onclick="return confirm('Bạn chắc chắn muốn thay đổi trạng thái sân này?')"
                        >
                            @if ($court->status === 'active')
                                <i class="fas fa-eye-slash"></i> Ẩn sân
                            @else
                                <i class="fas fa-check"></i> Kích hoạt
                            @endif
                        </button>
                    </form>

                    <!-- Delete Form -->
                    <form method="POST" action="{{ route('admin.courts.destroy', $court) }}" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button 
                            type="submit" 
                            class="btn btn-danger"
                            onclick="return confirm('Bạn chắc chắn muốn xóa sân này?')"
                        >
                            <i class="fas fa-trash"></i> Xóa sân
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="col-md-4">
            <!-- Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Thống kê</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="text-muted mb-1">Tổng ca giờ</div>
                        <div class="h4 mb-0">{{ $court->timeSlots->count() }}</div>
                    </div>
                    <div class="mb-3">
                        <div class="text-muted mb-1">Tổng lịch đặt</div>
                        <div class="h4 mb-0">{{ $court->bookings->count() }}</div>
                    </div>
                    <div>
                        <div class="text-muted mb-1">Lịch đặt chưa hoàn thành</div>
                        <div class="h4 mb-0 text-warning">
                            {{ $court->bookings->where('status', '!=', 'completed')->where('status', '!=', 'cancelled')->count() }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h5 class="mb-0"><i class="fas fa-link"></i> Liên kết nhanh</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.courts.index', ['venue_id' => $court->venue_id]) }}" class="btn btn-sm btn-outline-primary d-block mb-2">
                        <i class="fas fa-list"></i> Xem sân khác cùng cơ sở
                    </a>
                    <a href="{{ route('admin.venues.index') }}" class="btn btn-sm btn-outline-secondary d-block">
                        <i class="fas fa-building"></i> Quản lý cơ sở sân
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Time Slots Section -->
    @if ($court->timeSlots->count() > 0)
        <div class="card shadow mt-4">
            <div class="card-header py-3">
                <h5 class="mb-0"><i class="fas fa-clock"></i> Ca giờ hoạt động ({{ $court->timeSlots->count() }} ca)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ngày</th>
                                <th>Thời gian</th>
                                <th>Giá</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($court->timeSlots->take(10) as $slot)
                                <tr>
                                    <td>{{ $slot->day_of_week }}</td>
                                    <td>{{ $slot->start_time }} - {{ $slot->end_time }}</td>
                                    <td>
                                        @php
                                            $price = $slot->prices->first();
                                        @endphp
                                        {{ number_format($price?->price ?? 0, 0, '.', ',') }} ₫
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($court->timeSlots->count() > 10)
                    <small class="text-muted">... và {{ $court->timeSlots->count() - 10 }} ca giờ khác</small>
                @endif
            </div>
        </div>
    @endif

    <!-- Recent Bookings Section -->
    @if ($court->bookings->count() > 0)
        <div class="card shadow mt-4">
            <div class="card-header py-3">
                <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Lịch đặt gần đây ({{ $court->bookings->count() }} đơn)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Khách hàng</th>
                                <th>Ngày</th>
                                <th>Thời gian</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($court->bookings->sortByDesc('created_at')->take(10) as $booking)
                                <tr>
                                    <td><small class="badge bg-secondary">{{ $booking->id }}</small></td>
                                    <td>{{ $booking->user->name ?? 'N/A' }}</td>
                                    <td>{{ $booking->date }}</td>
                                    <td>{{ $booking->start_time }} - {{ $booking->end_time }}</td>
                                    <td>
                                        <span class="badge bg-{{ $booking->status === 'confirmed' ? 'success' : 'warning' }}">
                                            {{ $booking->status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($court->bookings->count() > 10)
                    <small class="text-muted">... và {{ $court->bookings->count() - 10 }} lịch đặt khác</small>
                @endif
            </div>
        </div>
    @endif
</div>

@endsection
