@extends('admin.layouts.app')

@section('title', 'Quản lý Sân')

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-list-check"></i> Quản lý Danh sách Sân
                </h1>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-primary text-uppercase mb-1">Tổng cộng</div>
                    <div class="h3 mb-0">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-success text-uppercase mb-1">Hoạt động</div>
                    <div class="h3 mb-0">{{ $stats['active'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-danger text-uppercase mb-1">Đã ẩn</div>
                    <div class="h3 mb-0">{{ $stats['inactive'] }}</div>
                </div>
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

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filter Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="mb-0"><i class="fas fa-filter"></i> Tìm kiếm & Lọc</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.courts.index') }}" class="row g-3">
                <!-- Search Input -->
                <div class="col-md-4">
                    <label for="search" class="form-label">Tìm kiếm theo tên sân</label>
                    <input 
                        type="text" 
                        id="search" 
                        name="search" 
                        class="form-control" 
                        placeholder="Nhập tên sân..."
                        value="{{ request('search') }}"
                    >
                </div>

                <!-- Status Filter -->
                <div class="col-md-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">-- Tất cả --</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                            ✓ Hoạt động
                        </option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                            ✗ Đã ẩn
                        </option>
                    </select>
                </div>

                <!-- Venue Filter -->
                <div class="col-md-3">
                    <label for="venue_id" class="form-label">Cơ sở sân</label>
                    <select id="venue_id" name="venue_id" class="form-select">
                        <option value="">-- Tất cả --</option>
                        @foreach ($venues as $venue)
                            <option value="{{ $venue->id }}" {{ request('venue_id') == $venue->id ? 'selected' : '' }}>
                                {{ $venue->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Search Button -->
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Tìm kiếm
                    </button>
                    <a href="{{ route('admin.courts.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Courts Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h5 class="mb-0"><i class="fas fa-table"></i> Danh sách Sân</h5>
        </div>
        <div class="card-body">
            @if ($courts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>ID</th>
                                <th>Tên Sân</th>
                                <th>Cơ sở</th>
                                <th>Chủ sân</th>
                                <th>Địa chỉ</th>
                                <th style="width: 12%;">Trạng thái</th>
                                <th style="width: 20%;">Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($courts as $court)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="form-check-input court-checkbox" value="{{ $court->id }}">
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $court->id }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $court->name }}</strong>
                                    </td>
                                    <td>
                                        {{ $court->venue->name ?? 'N/A' }}
                                    </td>
                                    <td>
                                        {{ $court->venue?->owner->name ?? 'N/A' }}
                                    </td>
                                    <td>
                                        <small>{{ $court->venue->address ?? 'N/A' }}</small>
                                    </td>
                                    <td>
                                        @if ($court->status === 'active')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle"></i> Hoạt động
                                            </span>
                                        @else
                                            <span class="badge bg-danger">
                                                <i class="fas fa-ban"></i> Đã ẩn
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <!-- View Button -->
                                        <a 
                                            href="{{ route('admin.courts.show', $court) }}" 
                                            class="btn btn-sm btn-info" 
                                            title="Xem chi tiết"
                                        >
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        <!-- Toggle Status Button -->
                                        <form 
                                            method="POST" 
                                            action="{{ route('admin.courts.toggle-status', $court) }}" 
                                            class="d-inline toggle-form"
                                        >
                                            @csrf
                                            @method('PATCH')
                                            <button 
                                                type="submit" 
                                                class="btn btn-sm {{ $court->status === 'active' ? 'btn-warning' : 'btn-success' }}"
                                                onclick="return confirm('Bạn chắc chắn muốn thay đổi trạng thái sân này?')"
                                                title="{{ $court->status === 'active' ? 'Ẩn sân' : 'Kích hoạt' }}"
                                            >
                                                @if ($court->status === 'active')
                                                    <i class="fas fa-eye-slash"></i> Ẩn
                                                @else
                                                    <i class="fas fa-check"></i> Kích hoạt
                                                @endif
                                            </button>
                                        </form>

                                        <!-- Edit Button -->
                                        <a 
                                            href="javascript:void(0)" 
                                            class="btn btn-sm btn-primary"
                                            onclick="editCourt({{ $court->id }}, '{{ $court->name }}', '{{ $court->status }}')"
                                            title="Chỉnh sửa"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <!-- Delete Button -->
                                        <form 
                                            method="POST" 
                                            action="{{ route('admin.courts.destroy', $court) }}" 
                                            class="d-inline"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="submit" 
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('Bạn chắc chắn muốn xóa sân này? Hành động này không thể hoàn tác!')"
                                                title="Xóa"
                                            >
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Batch Actions -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <form method="POST" action="{{ route('admin.courts.batch-update-status') }}" class="d-flex gap-2">
                            @csrf
                            <input type="hidden" id="batchCourtIds" name="court_ids" value="">
                            <select name="status" id="batchStatus" class="form-select form-select-sm" style="max-width: 150px;">
                                <option value="">-- Chọn trạng thái --</option>
                                <option value="active">Kích hoạt</option>
                                <option value="inactive">Ẩn</option>
                            </select>
                            <button 
                                type="submit" 
                                class="btn btn-sm btn-primary" 
                                id="batchApplyBtn"
                                disabled
                            >
                                Áp dụng
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Pagination -->
                <nav class="mt-4">
                    {{ $courts->links() }}
                </nav>
            @else
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle"></i> Không tìm thấy sân nào.
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Edit Court Modal -->
<div class="modal fade" id="editCourtModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chỉnh sửa sân</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCourtForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="editCourtName" class="form-label">Tên sân</label>
                        <input 
                            type="text" 
                            id="editCourtName" 
                            name="name" 
                            class="form-control"
                            required
                        >
                    </div>
                    <div class="mb-3">
                        <label for="editCourtStatus" class="form-label">Trạng thái</label>
                        <select id="editCourtStatus" name="status" class="form-select" required>
                            <option value="active">✓ Hoạt động</option>
                            <option value="inactive">✗ Đã ẩn</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Edit court function
    function editCourt(id, name, status) {
        const form = document.getElementById('editCourtForm');
        form.action = `/admin/courts/${id}`;
        document.getElementById('editCourtName').value = name;
        document.getElementById('editCourtStatus').value = status;
        new bootstrap.Modal(document.getElementById('editCourtModal')).show();
    }

    // Select all checkboxes
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.court-checkbox');
        checkboxes.forEach(checkbox => checkbox.checked = this.checked);
        updateBatchButton();
    });

    // Update batch button state
    document.querySelectorAll('.court-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBatchButton);
    });

    function updateBatchButton() {
        const checkedBoxes = document.querySelectorAll('.court-checkbox:checked');
        const courtIds = Array.from(checkedBoxes).map(cb => cb.value).join(',');
        document.getElementById('batchCourtIds').value = courtIds;
        document.getElementById('batchApplyBtn').disabled = checkedBoxes.length === 0;
    }
</script>

@endsection
