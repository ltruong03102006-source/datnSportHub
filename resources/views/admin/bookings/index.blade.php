@extends('admin.layouts.app')

@section('title', 'Quản Lý Lịch Đặt Sân')
@section('header_title', 'Lịch Đặt Toàn Hệ Thống')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h5 class="mb-0">Tất cả lịch đặt (Bookings)</h5>
        
        <form action="{{ route('admin.bookings.index') }}" method="GET" class="d-flex gap-2">
            <select name="status" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                <option value="">Tất cả trạng thái</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Đã hoàn thành</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
            </select>
            <div class="input-group input-group-sm" style="width: 250px;">
                <input type="text" name="search" class="form-control" placeholder="Tìm người đặt, tên sân..." value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit"><i class="fa-solid fa-search"></i></button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Mã Đơn</th>
                        <th>Khách Đặt</th>
                        <th>Sân / Cơ Sở</th>
                        <th>Thời Gian</th>
                        <th>Tổng Tiền</th>
                        <th>Trạng Thái</th>
                        <th class="text-center pe-4">Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td class="ps-4 text-muted fw-bold">#{{ $booking->id }}</td>
                            <td>
                                @if($booking->user)
                                    <span class="fw-medium text-dark">{{ $booking->user->name }}</span><br>
                                    <small class="text-muted">{{ $booking->user->email }}</small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($booking->court && $booking->court->venue)
                                    <span class="fw-medium">{{ $booking->court->venue->name }}</span><br>
                                    <small class="text-muted">Sân con: {{ $booking->court->name }}</small>
                                @else
                                    <span class="text-muted">Sân không tồn tại</span>
                                @endif
                            </td>
                            <td>
                                <div><i class="fa-regular fa-calendar me-1 text-muted"></i> {{ $booking->slot_date ? $booking->slot_date->format('d/m/Y') : 'N/A' }}</div>
                                <div><i class="fa-regular fa-clock me-1 text-muted"></i> {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}</div>
                            </td>
                            <td class="fw-bold text-success">{{ number_format($booking->total_price) }}đ</td>
                            <td>
                                @php
                                    $statusColor = 'secondary';
                                    $statusText = $booking->status;
                                    switch($booking->status) {
                                        case 'pending': $statusColor = 'warning text-dark'; $statusText = 'Chờ duyệt'; break;
                                        case 'confirmed': $statusColor = 'info text-dark'; $statusText = 'Đã xác nhận'; break;
                                        case 'completed': $statusColor = 'success'; $statusText = 'Đã hoàn thành'; break;
                                        case 'cancelled': $statusColor = 'danger'; $statusText = 'Đã hủy'; break;
                                    }
                                @endphp
                                <span class="badge bg-{{ $statusColor }}">{{ $statusText }}</span>
                            </td>
                            <td class="text-center pe-4">
                                <button class="btn btn-sm btn-outline-info" title="Xem chi tiết"><i class="fa-solid fa-eye"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Không tìm thấy đơn đặt sân nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($bookings->hasPages())
    <div class="card-footer bg-white border-top-0 pt-3">
        {{ $bookings->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
