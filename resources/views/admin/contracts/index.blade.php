@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent px-0 mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Hợp đồng</li>
                </ol>
            </nav>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h1 class="h3 mb-1">Quản lý hợp đồng</h1>
                    <p class="text-muted mb-0">Quản lý trạng thái và thông tin hợp đồng giữa Admin và Chủ sân.</p>
                </div>
                <a href="{{ route('admin.contracts.create') }}" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-lg me-2"></i> Tạo hợp đồng
                </a>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.contracts.index') }}">
                        <div class="row g-3 align-items-end">
                            <div class="col-12 col-md-4">
                                <label class="form-label">Tìm kiếm</label>
                                <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control" placeholder="Mã, tiêu đề hoặc tên chủ sân">
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label">Trạng thái</label>
                                <select name="status" class="form-select">
                                    <option value="">Tất cả</option>
                                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                                    <option value="accepted" {{ request('status') === 'accepted' ? 'selected' : '' }}>Accepted</option>
                                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="terminated" {{ request('status') === 'terminated' ? 'selected' : '' }}>Terminated</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label">Chủ sân</label>
                                <select name="owner_id" class="form-select">
                                    <option value="">Tất cả chủ sân</option>
                                    @foreach($owners as $owner)
                                        <option value="{{ $owner->id }}" {{ request('owner_id') == $owner->id ? 'selected' : '' }}>{{ $owner->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label">Từ ngày</label>
                                <input type="date" name="start_date_from" value="{{ request('start_date_from') }}" class="form-control">
                            </div>
                            <div class="col-6 col-md-2">
                                <label class="form-label">Đến ngày</label>
                                <input type="date" name="start_date_to" value="{{ request('start_date_to') }}" class="form-control">
                            </div>
                            <div class="col-12 col-md-2">
                                <label class="form-label">Sắp xếp</label>
                                <select name="sort" class="form-select">
                                    <option value="newest" {{ request('sort', 'newest') === 'newest' ? 'selected' : '' }}>Mới nhất</option>
                                    <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Cũ nhất</option>
                                </select>
                            </div>
                            <div class="col-12 d-flex flex-column flex-sm-row justify-content-end gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search me-2"></i> Tìm kiếm
                                </button>
                                <a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-counterclockwise me-2"></i> Đặt lại
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    @if($contracts->isEmpty())
                        <div class="text-center py-5">
                            <div class="mb-3 text-muted" style="font-size: 3rem;">+</div>
                            <h5 class="mb-2">Chưa có hợp đồng nào.</h5>
                            <p class="text-muted mb-0">Tạo hợp đồng mới để quản lý hợp tác với chủ sân.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">STT</th>
                                        <th scope="col">Mã hợp đồng</th>
                                        <th scope="col">Chủ sân</th>
                                        <th scope="col">Admin tạo</th>
                                        <th scope="col">Tiêu đề</th>
                                        <th scope="col">Hoa hồng</th>
                                        <th scope="col">Bắt đầu</th>
                                        <th scope="col">Kết thúc</th>
                                        <th scope="col">Trạng thái</th>
                                        <th scope="col">Ngày tạo</th>
                                        <th scope="col">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($contracts as $index => $contract)
                                        <tr>
                                            <td>{{ $contracts->firstItem() + $index }}</td>
                                            <td class="text-nowrap">{{ $contract->contract_code }}</td>
                                            <td>{{ $contract->owner?->name ?? '-' }}</td>
                                            <td>{{ $contract->creator?->name ?? '-' }}</td>
                                            <td>{{ $contract->title }}</td>
                                            <td>{{ number_format($contract->commission_rate, 2) }}%</td>
                                            <td>{{ $contract->start_date?->format('Y-m-d') }}</td>
                                            <td>{{ $contract->end_date?->format('Y-m-d') }}</td>
                                            <td>
                                                @php
                                                    $badge = match($contract->status) {
                                                        'draft' => 'secondary',
                                                        'sent' => 'primary',
                                                        'accepted' => 'success',
                                                        'rejected' => 'danger',
                                                        'expired' => 'warning',
                                                        'terminated' => 'dark',
                                                        default => 'secondary',
                                                    };
                                                @endphp
                                                <span class="badge bg-{{ $badge }} text-capitalize">{{ $contract->status }}</span>
                                            </td>
                                            <td>{{ $contract->created_at->format('Y-m-d H:i') }}</td>
                                            <td class="text-nowrap">
                                                <a href="{{ route('admin.contracts.show', $contract) }}" class="btn btn-sm btn-outline-primary me-1 mb-1">Xem</a>
                                                <a href="{{ route('admin.contracts.edit', $contract) }}" class="btn btn-sm btn-outline-secondary me-1 mb-1">Sửa</a>
                                                @if(in_array($contract->status, ['draft', 'rejected'], true))
                                                    <form action="{{ route('admin.contracts.send', $contract) }}" method="POST" class="d-inline mb-1" onsubmit="return confirm('Bạn có chắc chắn muốn gửi hợp đồng này?');">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success">Gửi</button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="card-footer bg-white border-0 d-flex justify-content-center py-3">
                            {{ $contracts->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
