<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết điểm sân: {{ $venue->name }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }

        .card-shell {
            border: 0;
            border-radius: 18px;
            box-shadow: 0 12px 35px rgba(15, 23, 42, 0.08);
        }

        #map-view {
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            z-index: 1;
        }

        .sporthub-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 18px;
            font-size: 14px;
            font-weight: 700;
            color: white;
            background: #059669;
            border-radius: 10px;
            text-decoration: none;
            border: 0;
            transition: all .2s ease;
        }

        .sporthub-btn-primary:hover {
            background: #047857;
            color: white;
        }

        .sporthub-btn-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 700;
            color: #475569;
            background: white;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            text-decoration: none;
            transition: all .2s ease;
        }

        .sporthub-btn-outline:hover {
            color: #047857;
            border-color: #10b981;
            background: #ecfdf5;
        }

        .sporthub-btn-warning {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 700;
            color: #713f12;
            background: #fef3c7;
            border: 1px solid #fde68a;
            border-radius: 10px;
            text-decoration: none;
            transition: all .2s ease;
        }

        .sporthub-btn-warning:hover {
            background: #fde68a;
            color: #713f12;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 800;
            margin-left: 8px;
            vertical-align: middle;
        }

        .status-approved {
            background: #dcfce7;
            color: #166534;
        }

        .status-rejected {
            background: #fee2e2;
            color: #b91c1c;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }
        .sporthub-nav {
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
    padding: 16px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 50;
}

.sporthub-nav-left {
    display: flex;
    align-items: center;
    gap: 24px;
}

.sporthub-logo {
    font-size: 28px;
    line-height: 1;
    font-weight: 800;
    background: linear-gradient(to right, #059669, #14b8a6);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    white-space: nowrap;
}

.sporthub-breadcrumb {
    display: flex;
    align-items: center;
    gap: 10px;
    padding-left: 20px;
    border-left: 1px solid #e2e8f0;
    font-size: 14px;
    color: #64748b;
}

.sporthub-breadcrumb a,
.sporthub-nav-right a {
    color: #475569;
    text-decoration: none;
    font-weight: 600;
    transition: color .2s ease;
}

.sporthub-breadcrumb a:hover,
.sporthub-nav-right a:hover {
    color: #059669;
    text-decoration: none;
}

.sporthub-breadcrumb span:last-child {
    color: #1e293b;
    font-weight: 700;
}

.sporthub-nav-right {
    display: flex;
    align-items: center;
    gap: 28px;
    font-size: 14px;
}

@media (max-width: 768px) {
    .sporthub-nav {
        flex-direction: column;
        align-items: flex-start;
        gap: 14px;
    }

    .sporthub-nav-left {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .sporthub-breadcrumb {
        border-left: 0;
        padding-left: 0;
        flex-wrap: wrap;
    }

    .sporthub-nav-right {
        gap: 18px;
    }
}
    </style>
</head>

<body class="text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation -->
<nav class="sporthub-nav">
    <div class="sporthub-nav-left">
        <div class="sporthub-logo">
            SportHub
        </div>

        <div class="sporthub-breadcrumb">
            <a href="{{ route('owner.dashboard') }}">
                Dashboard
            </a>
            <span>/</span>
            <a href="{{ route('owner.web.venues.index') }}">
                Quản lý cơ sở
            </a>
            <span>/</span>
            <span>{{ $venue->name }}</span>
        </div>
    </div>

    <div class="sporthub-nav-right">
        <a href="{{ route('owner.dashboard') }}">
            Tổng quan
        </a>

        <a href="{{ route('owner.web.calendar.index') }}">
            Lịch đặt sân
        </a>
    </div>
</nav>

    <div class="flex-1 p-6 lg:p-10 max-w-7xl mx-auto w-full">

        <!-- Header -->
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-8 gap-4">
            <div>
                <h2 class="text-3xl font-bold text-slate-800 mb-2">
                    {{ $venue->name }}

                    @if($venue->status === 'approved')
                        <span class="status-badge status-approved">
                            Hoạt động
                        </span>
                    @elseif($venue->status === 'rejected')
                        <span class="status-badge status-rejected">
                            Bị từ chối
                        </span>
                    @else
                        <span class="status-badge status-pending">
                            Chờ duyệt
                        </span>
                    @endif
                </h2>

                <p class="text-slate-500">
                    Thông tin chi tiết điểm sân và quản lý các sân con.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <button onclick="toggleRulesCard()" class="sporthub-btn-primary">
                    Nội quy sân
                </button>

                <a href="{{ route('owner.web.reviews.index', ['venue_id' => $venue->id]) }}"
                   class="sporthub-btn-warning">
                    Xem đánh giá
                </a>

                <a href="{{ route('owner.web.venues.edit', $venue->id) }}"
                   class="sporthub-btn-outline">
                    Sửa thông tin
                </a>

                <a href="{{ route('owner.web.venues.index') }}"
                   class="sporthub-btn-outline">
                    Quay lại
                </a>
            </div>
        </div>
    <div id="rulesCard" class="card card-shell mb-4" style="display: none;">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3" style="border-top-left-radius: 18px; border-top-right-radius: 18px;">
            <h5 class="mb-0 fw-bold">Nội quy cơ sở & Quy định sân</h5>
        </div>
        <div class="card-body p-4">  
            <textarea id="venueRulesInput" rows="5" class="form-control mb-3" placeholder="Bạn có thể tự nhập thêm nội quy tại đây (Mỗi luật 1 dòng)...">{{ $venue->rules }}</textarea>
            
            <div class="text-end">
                <button type="button" id="btnSaveRules" class="btn btn-primary fw-bold px-4">Lưu Nội quy</button>
            </div>
        </div>
    </div>
    <div class="card card-shell mb-4">
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-12 col-lg-6">
                    @if($venue->banner)
                        <img src="{{ asset('storage/' . $venue->banner) }}" class="img-fluid rounded-3 mb-3 w-100" style="height: 250px; object-fit: cover;" alt="Banner">
                    @else
                        <div class="bg-light rounded-3 mb-3 d-flex align-items-center justify-content-center text-muted" style="height: 250px;">Chưa có ảnh Banner</div>
                    @endif
                    
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Thông tin cơ bản</h5>
                    <div class="mb-2"><strong>Môn thể thao:</strong> <span class="badge bg-info text-dark">{{ $venue->sport?->name ?? 'Chưa cập nhật' }}</span></div>
                    <div class="mb-2"><strong>Địa chỉ:</strong> {{ $venue->address }}</div>
                    <div class="mb-2"><strong>Mô tả:</strong> <br> {!! nl2br(e($venue->description)) !!}</div>
                </div>

                <div class="col-12 col-lg-6">
                    <h5 class="fw-bold text-dark border-bottom pb-2 mb-3">Vị trí bản đồ</h5>
                    <div id="map-view" style="height: 300px;" class="mb-2"></div>
                    <div class="d-flex gap-3">
                        <div class="text-muted small"><strong>Vĩ độ:</strong> {{ $venue->lat }}</div>
                        <div class="text-muted small"><strong>Kinh độ:</strong> {{ $venue->lng }}</div>
                    </div>
                </div>
                <h5 class="fw-bold text-dark border-bottom pb-2 mt-4 mb-3">Thư viện ảnh</h5>
                    @if($venue->images && $venue->images->count() > 0)
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($venue->images as $img)
                                <img src="{{ asset('storage/' . $img->image_path) }}" class="rounded-3 border" style="height: 90px; width: 120px; object-fit: cover;">
                            @endforeach
                        </div>
                    @else
                        <div class="text-muted small">Chưa có ảnh nào trong thư viện.</div>
                    @endif
            </div>
        </div>
    </div>
    <div class="card card-shell mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3" style="border-top-left-radius: 18px; border-top-right-radius: 18px;">
            <h5 class="mb-0 fw-bold">Chính sách hủy sân & Phí phạt</h5>
        </div>
        <div class="card-body p-4">
            <div class="alert alert-info py-2 mb-4" style="font-size: 0.9rem;">
                💡 <b>Mẹo:</b> Hệ thống quét từ mốc sát giờ nhất. Nếu khách hủy lọt vào khoảng thời gian nào, hệ thống thu phí tương ứng. Nếu hủy trước tất cả các mốc (hoặc bạn không cài đặt), khách được <b>hoàn 100% (Phạt 0%)</b>.
            </div>
            
            <form id="formAddPolicy" class="row g-2 align-items-end mb-4">
                <div class="col-12 col-md-5">
                    <label class="form-label fw-semibold text-sm">Khách tự hủy trước (Số giờ) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="pol_hours" required min="0" placeholder="VD: 24">
                </div>
                <div class="col-12 col-md-5">
                    <label class="form-label fw-semibold text-sm">Chịu phí phạt (%) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="pol_fee" required min="0" max="100" placeholder="VD: 30">
                </div>
                <div class="col-12 col-md-2">
                    <button type="submit" id="btnAddPolicy" class="btn btn-primary w-100 fw-bold">Thêm mốc</button>
                </div>
            </form>

            <div class="table-responsive border rounded-3">
                <table class="table table-borderless table-striped align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th class="py-3 px-4">Điều kiện hủy</th>
                            <th class="py-3 px-4 text-center">Phí phạt hệ thống thu</th>
                            <th class="py-3 px-4 text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="policyList">
                        <tr><td colspan="3" class="text-center text-muted py-4">Đang tải cấu hình...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="card card-shell mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3" style="border-top-left-radius: 18px; border-top-right-radius: 18px;">
            <h5 class="mb-0 fw-bold">Danh sách Sân con</h5>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createCourtModal">
                + Tạo Sân con
            </button>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush rounded-bottom" style="border-bottom-left-radius: 18px; border-bottom-right-radius: 18px;">
                @forelse($venue->courts as $court)
                    <div class="list-group-item py-4 px-4">
                        
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3">
                            
                            <div class="mb-2 mb-md-0">
                                <h6 class="mb-1 fw-bold text-dark fs-5">{{ $court->name }}</h6>
                                <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
                                    <span class="text-muted small">Trạng thái:</span>
                                    @if($court->status === 'active')
                                        <span class="badge bg-success">Hoạt động</span>
                                    @else
                                        <span class="badge bg-secondary">Bảo trì</span>
                                    @endif
                                    
                                    <span class="text-muted small ms-2 border-start ps-2">Tổng ca: <strong class="text-dark">{{ $court->timeSlots->count() ?? 0 }}</strong></span>
                                </div>
                            </div>
                            
                            <div class="d-flex flex-wrap gap-2 justify-content-md-end align-items-center">
                                
                                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#generateSlotsModal" onclick="setGenerateCourtId({{ $court->id }}, '{{ $court->name }}')">
                                    Sinh ca tự động
                                </button>

                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#manualSlotModal" onclick="setManualCourtId({{ $court->id }}, '{{ $court->name }}')">
                                    + Thêm ca lẻ
                                </button>

                                <button class="btn btn-sm btn-outline-warning text-dark fw-medium" data-bs-toggle="modal" data-bs-target="#lockSlotModal" onclick="setLockCourtId({{ $court->id }}, '{{ $court->name }}')">
                                    <i class="fa-solid fa-lock"></i> Khóa ca
                                </button>
                                
                                <div class="vr mx-1 d-none d-md-block text-secondary" style="width: 1.5px; opacity: 0.3;"></div>

                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editCourtModal" data-id="{{ $court->id }}" data-name="{{ $court->name }}" data-status="{{ $court->status }}" onclick="populateCourtEditModal(this)">
                                    Sửa
                                </button>

                                <button class="btn btn-sm btn-outline-danger" onclick="deleteCourt({{ $court->id }}, '{{ $court->name }}')">
                                    Xóa
                                </button>

                            </div>
                        </div>

                        @php
                            // Tự động lọc: Bỏ qua các ca khóa ở ngày hôm qua, VÀ bỏ qua các ca khóa hôm nay nhưng giờ kết thúc đã qua đi
                            $activeLocks = $court->courtLocks()
                                ->where(function($query) {
                                    $query->where('lock_date', '>', now()->toDateString())
                                          ->orWhere(function($q) {
                                              $q->where('lock_date', now()->toDateString())
                                                ->where('end_time', '>', now()->format('H:i:s'));
                                          });
                                })
                                ->orderBy('lock_date')
                                ->orderBy('start_time')
                                ->get();
                        @endphp

                        @if($activeLocks->count() > 0)
                            <div class="mt-4 p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3">
                                <h6 class="text-danger fw-bold mb-2" style="font-size: 0.9rem;">
                                    <i class="fa-solid fa-triangle-exclamation me-1"></i> Lịch đang Khóa / Bảo trì:
                                </h6>
                                <ul class="list-group list-group-flush gap-2">
                                    @foreach($activeLocks as $lock)
                                        <li class="list-group-item d-flex justify-content-between align-items-center rounded bg-white shadow-sm border-0" style="border-left: 4px solid #dc3545 !important;">
                                            <div>
                                                <span class="badge bg-danger mb-1">{{ \Carbon\Carbon::parse($lock->lock_date)->format('d/m/Y') }}</span>
                                                <strong class="text-dark ms-2">{{ substr($lock->start_time, 0, 5) }} - {{ substr($lock->end_time, 0, 5) }}</strong>
                                                <div class="text-muted mt-1" style="font-size: 0.85rem;"><i class="fa-solid fa-quote-left me-1 opacity-50"></i> {{ $lock->reason }}</div>
                                            </div>
                                            <button class="btn btn-sm btn-outline-success fw-bold px-3" onclick="unlockSlot({{ $lock->id }})">Mở khóa</button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                    </div>
                @empty
                    <div class="list-group-item py-5 text-center text-muted bg-light">
                        <div class="mb-2 fs-2">📭</div>
                        Chưa có sân con nào. Vui lòng tạo mới!
                    </div>
                @endforelse
            </div>
        </div>
        </div>
    </div>
</div>

<div class="modal fade" id="createCourtModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 14px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Tạo Sân con mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="createCourtForm">
                    <div class="mb-3">
                        <label for="courtName" class="form-label fw-semibold">Tên Sân <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="courtName" name="name" required placeholder="VD: Sân 5A">
                        <div class="invalid-feedback" id="error-name"></div>
                    </div>
                    <div class="mb-3">
                        <label for="courtStatus" class="form-label fw-semibold">Trạng thái</label>
                        <select class="form-select" id="courtStatus" name="status">
                            <option value="active">Hoạt động</option>
                            <option value="inactive">Bảo trì</option>
                        </select>
                        <div class="invalid-feedback" id="error-status"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="btnSaveCourt">Lưu lại</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editCourtModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 14px;">
            <form id="editCourtForm">
                @csrf
                <input type="hidden" id="edit_court_id">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Chỉnh sửa sân con</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body space-y-3">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên sân con <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_court_name" required>
                        <div class="invalid-feedback" id="error-edit-name"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Trạng thái hoạt động</label>
                        <select class="form-select" id="edit_court_status">
                            <option value="active">Hoạt động</option>
                            <option value="inactive">Bảo trì</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="btnUpdateCourt">Lưu thay đổi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="generateSlotsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 14px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Sinh ca tự động: <span id="generateCourtName" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2" style="font-size: 0.9rem;">
                    💡 Hệ thống sẽ tự động tạo các ca liên tiếp. Bạn có thể sử dụng nhiều lần để tạo các khung giờ khác nhau (VD: Sáng, Chiều). Các ca bị trùng sẽ tự động được gộp hoặc bỏ qua.
                </div>
                <form id="generateSlotsForm">
                    <input type="hidden" id="generateCourtId">
                    
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Giờ mở cửa <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="open_time" required value="06:00">
                            <div class="invalid-feedback" id="error-open_time"></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Giờ đóng cửa <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="close_time" required value="22:00">
                            <div class="invalid-feedback" id="error-close_time"></div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Thời lượng mỗi ca (phút) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="duration" min="30" step="5" value="" required placeholder="Nhập số phút (VD: 30, 60, 90, 120)">
                        <div class="invalid-feedback" id="error-duration"></div>
                    </div>

                    <h6 class="fw-bold mt-4 mb-3 border-bottom pb-2">Thiết lập giá & Giờ cao điểm</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Giá giờ thường <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="regular_price" min="0" required placeholder="VD: 100000">
                            <div class="invalid-feedback" id="error-regular_price"></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Giá giờ cao điểm <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="peak_price" min="0" required placeholder="VD: 150000">
                            <div class="invalid-feedback" id="error-peak_price"></div>
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Bắt đầu cao điểm <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="peak_start_time" required value="17:00">
                            <div class="invalid-feedback" id="error-peak_start_time"></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Kết thúc cao điểm <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="peak_end_time" required value="20:00">
                            <div class="invalid-feedback" id="error-peak_end_time"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-primary" id="btnGenerateSlots">Bắt đầu tạo ca</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="manualSlotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 14px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Thêm ca lẻ cho: <span id="manualCourtName" class="text-success"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="manualSlotForm">
                    <input type="hidden" id="manualCourtId">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Giờ bắt đầu <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="start_time" required value="06:00">
                            <div class="invalid-feedback" id="error-manual-start_time"></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Giờ kết thúc <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="end_time" required value="22:00">
                            <div class="invalid-feedback" id="error-manual-end_time"></div>
                        </div>
                    </div>
                    
                    <div class="row g-3 mt-1">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Giá ca này <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="price" required min="0" placeholder="VD: 100000">
                            <div class="invalid-feedback" id="error-manual-price"></div>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Loại giá</label>
                            <select class="form-select" name="price_type">
                                <option value="normal">Giờ thường</option>
                                <option value="peak">Giờ cao điểm</option>
                            </select>
                            <div class="invalid-feedback" id="error-manual-price_type"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-success text-white" id="btnSaveManualSlot">Lưu ca</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="lockSlotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 14px;">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Khóa sân (Bảo trì): <span id="lockCourtName" class="text-warning"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="lockSlotForm">
                    <input type="hidden" id="lockCourtId">
                    <input type="hidden" name="selected_slots" id="selectedSlotsInput">
                    
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Chọn ngày khóa <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-lg bg-light" name="lock_date" id="lockDateInput" required min="{{ now()->toDateString() }}">
                        <div class="invalid-feedback" id="error-lock-lock_date"></div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark">
                            <i class="fa-solid fa-calendar-day text-primary me-1"></i> Sơ đồ ca sân trong ngày
                        </label>
                        
                        <div class="d-flex flex-wrap gap-3 mb-2" style="font-size: 0.8rem; font-weight: 600;">
                            <span class="d-flex align-items-center gap-1"><span class="d-inline-block border rounded-circle shadow-sm" style="width:12px;height:12px;background:#fff;"></span> Trống</span>
                            <span class="d-flex align-items-center gap-1"><span class="d-inline-block rounded-circle" style="width:12px;height:12px;background:#0d6efd;"></span> Đang chọn</span>
                            <span class="d-flex align-items-center gap-1"><span class="d-inline-block border border-danger-subtle rounded-circle" style="width:12px;height:12px;background:#f8d7da;"></span> Có khách</span>
                            <span class="d-flex align-items-center gap-1"><span class="d-inline-block border border-warning-subtle rounded-circle" style="width:12px;height:12px;background:#fff3cd;"></span> Đang khóa</span>
                        </div>

                        <div id="lockSlotVisualizer" class="d-flex flex-wrap gap-2 p-3 rounded-4" style="min-height: 90px; background: #f8f9fa; border: 1px dashed #dee2e6;">
                            <span class="text-muted small m-auto">Vui lòng chọn ngày để xem lịch...</span>
                        </div>
                        <div class="invalid-feedback text-danger mt-1 d-block fw-bold" id="error-lock-selected_slots"></div>
                        <div class="alert alert-info py-2 mt-2 mb-0" style="font-size: 0.85rem;">
                            💡 Bạn có thể <b>click chọn các ca rời rạc</b> hoặc nhảy cóc qua các ca đã có người đặt để khóa!
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold">Lý do khóa sân <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="reason" required placeholder="VD: Sửa mặt sân, Nghỉ lễ...">
                        <div class="invalid-feedback" id="error-lock-reason"></div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-warning fw-bold text-dark" id="btnSaveLockSlot">Thực hiện Khóa</button>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    const csrfToken = '{{ csrf_token() }}';

    // --- KHỞI TẠO BẢN ĐỒ CHỈ ĐỌC ---
    const lat = {{ $venue->lat ?? 21.028511 }};
    const lng = {{ $venue->lng ?? 105.804817 }};

    const map = L.map('map-view', {
        zoomControl: true,
        dragging: false,
        scrollWheelZoom: false
    }).setView([lat, lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    L.marker([lat, lng]).addTo(map).bindPopup("<b>{{ $venue->name }}</b><br>{{ $venue->address }}").openPopup();

    // --- 1. LOGIC TẠO SÂN CON MỚI ---
    document.getElementById('btnSaveCourt').addEventListener('click', async function() {
        const form = document.getElementById('createCourtForm');
        const formData = new FormData(form);
        const submitBtn = this;
        
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.innerText = '');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang lưu...';

        try {
            const response = await fetch("{{ route('owner.web.courts.store', $venue->id) }}", {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData
            });

            const result = await response.json();
            if (response.ok) {
                window.location.reload(); 
            } else if (response.status === 422) {
                for (const key in result.errors) {
                    const input = form.querySelector(`[name="${key}"]`);
                    if (input) {
                        input.classList.add('is-invalid');
                        document.getElementById(`error-${key}`).innerText = result.errors[key][0];
                    }
                }
            } else {
                alert(result.message || 'Có lỗi xảy ra, vui lòng thử lại.');
            }
        } catch (error) {
            alert('Lỗi kết nối đến server.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Lưu lại';
        }
    });

    // --- 2. LOGIC SỬA SÂN CON ---
    function populateCourtEditModal(button) {
        document.getElementById('edit_court_id').value = button.dataset.id;
        document.getElementById('edit_court_name').value = button.dataset.name;
        document.getElementById('edit_court_status').value = button.dataset.status;
        
        document.getElementById('edit_court_name').classList.remove('is-invalid');
        document.getElementById('error-edit-name').innerText = '';
    }

    document.getElementById('editCourtForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const id = document.getElementById('edit_court_id').value;
        const btn = document.getElementById('btnUpdateCourt');
        
        document.getElementById('edit_court_name').classList.remove('is-invalid');
        document.getElementById('error-edit-name').innerText = '';

        btn.disabled = true; btn.textContent = 'Đang lưu...';

        try {
            const response = await fetch(`/owner/courts/${id}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    _method: 'PUT',
                    name: document.getElementById('edit_court_name').value,
                    status: document.getElementById('edit_court_status').value
                })
            });

            const result = await response.json();

            if (response.ok) {
                window.location.reload();
            } else if (response.status === 422) {
                document.getElementById('edit_court_name').classList.add('is-invalid');
                document.getElementById('error-edit-name').innerText = result.errors.name[0];
            } else {
                alert(result.message || 'Cập nhật thất bại.');
            }
        } catch (err) { 
            alert('Lỗi kết nối đến máy chủ.'); 
        } finally { 
            btn.disabled = false; btn.textContent = 'Lưu thay đổi'; 
        }
    });

    // --- 3. LOGIC XÓA SÂN CON ---
    async function deleteCourt(id, name) {
        if (!confirm(`Bạn có chắc chắn muốn xóa sân "${name}" không?\nLưu ý: Thao tác này sẽ xóa toàn bộ khung giờ của sân này!`)) return;

        try {
            const response = await fetch(`/owner/courts/${id}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ _method: 'DELETE' })
            });

            const result = await response.json();
            if (response.ok) {
                window.location.reload();
            } else {
                alert(result.message || 'Không thể xóa sân này.');
            }
        } catch (err) {
            alert('Lỗi kết nối đến máy chủ.');
        }
    }

    // --- 4. LOGIC SINH CA TỰ ĐỘNG ---
    let currentGenerateUrl = '';
    
    function setGenerateCourtId(courtId, courtName) {
        document.getElementById('generateCourtId').value = courtId;
        document.getElementById('generateCourtName').innerText = courtName;
        currentGenerateUrl = `{{ url('owner/courts') }}/${courtId}/generate-slots`;
    }

    document.getElementById('btnGenerateSlots').addEventListener('click', async function() {
        const form = document.getElementById('generateSlotsForm');
        const formData = new FormData(form);
        const submitBtn = this;
        
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.innerText = '');

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';

        try {
            const response = await fetch(currentGenerateUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData
            });

            const result = await response.json();

            if (response.ok) {
                alert(result.message);
                window.location.reload();
            } else if (response.status === 422) {
                if(result.errors) {
                    for (const key in result.errors) {
                        const input = form.querySelector(`[name="${key}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            document.getElementById(`error-${key}`).innerText = result.errors[key][0];
                        }
                    }
                } else {
                    alert(result.message); // Hiển thị thông báo lỗi custom (vd: thời lượng quá lớn)
                }
            } else if (response.status === 400) {
                alert(result.message); // Hiển thị cảnh báo sân đã có dữ liệu (nếu Controller còn trả về)
            } else {
                alert(result.message || 'Có lỗi xảy ra, vui lòng thử lại.');
            }
        } catch (error) {
            alert('Lỗi kết nối đến server.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerText = 'Bắt đầu tạo ca';
        }
    });

    // --- 5. LOGIC THÊM CA LẺ (TẠO CHAY) ---
    let currentManualUrl = '';
    function setManualCourtId(courtId, courtName) {
        document.getElementById('manualCourtId').value = courtId;
        document.getElementById('manualCourtName').innerText = courtName;
        currentManualUrl = `{{ url('owner/courts') }}/${courtId}/slots`;
    }

    document.getElementById('btnSaveManualSlot').addEventListener('click', async function() {
        const form = document.getElementById('manualSlotForm');
        const formData = new FormData(form);
        const submitBtn = this;
        
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        submitBtn.disabled = true; 
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang lưu...';

        try {
            const response = await fetch(currentManualUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData
            });
            
            const result = await response.json();
            
            if (response.ok) {
                alert(result.message); 
                window.location.reload();
            } else if (response.status === 422) {
                if(result.errors) {
                    for (const key in result.errors) {
                        const input = form.querySelector(`[name="${key}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            document.getElementById(`error-manual-${key}`).innerText = result.errors[key][0];
                        }
                    }
                } else {
                    alert(result.message); // Báo lỗi chồng lấn giờ
                }
            } else { 
                alert(result.message || 'Lưu thất bại.'); 
            }
        } catch (error) { 
            alert('Lỗi kết nối máy chủ.'); 
        } finally { 
            submitBtn.disabled = false; 
            submitBtn.innerText = 'Lưu ca'; 
        }
    });
    // --- LOGIC KHÓA SÂN & CHỌN NHIỀU CA (MULTI-SELECT) ---
    let currentLockUrl = '';
    let selectedLockSlots = []; // Mảng lưu các ca đang được click chọn

    function setLockCourtId(courtId, courtName) {
        document.getElementById('lockCourtName').innerText = courtName;
        document.getElementById('lockCourtId').value = courtId;
        currentLockUrl = `/owner/courts/${courtId}/lock`;
        
        // CẬP NHẬT: Reset lại mảng và input ẩn mới
        selectedLockSlots = []; 
        document.getElementById('selectedSlotsInput').value = '';

        const dateInput = document.getElementById('lockDateInput').value;
        fetchSlotsForLock(courtId, dateInput);
    }

    document.getElementById('lockDateInput').addEventListener('change', function() {
        const courtId = document.getElementById('lockCourtId').value;
        
        // CẬP NHẬT: Reset lại mảng và input ẩn mới khi đổi ngày
        selectedLockSlots = []; 
        document.getElementById('selectedSlotsInput').value = '';
        
        fetchSlotsForLock(courtId, this.value);
    });


    async function fetchSlotsForLock(courtId, dateStr) {
        const vis = document.getElementById('lockSlotVisualizer');
        vis.innerHTML = '<div class="m-auto"><span class="spinner-border spinner-border-sm text-primary"></span> <span class="text-muted small ms-2">Đang tải lịch...</span></div>';

        if(!dateStr || !courtId) return;

        try {
            const res = await fetch(`/api/courts/${courtId}/availability?date=${dateStr}`);
            const responseData = await res.json();
            
            if(responseData.data && responseData.data.length > 0) {
                vis.innerHTML = '';
                responseData.data.forEach(slot => {
                    const start = slot.start_time.substring(0,5);
                    const end = slot.end_time.substring(0,5);
                    
                    const badge = document.createElement('div');
                    // Style cơ bản của thẻ (Pill mềm mại)
                    badge.className = 'px-3 py-2 rounded-pill text-center user-select-none transition-all duration-200';
                    badge.style.fontSize = '0.85rem';
                    badge.style.fontWeight = '600';
                    
                    if(slot.is_past) {
                        badge.className += ' bg-secondary-subtle text-secondary border border-secondary-subtle text-decoration-line-through opacity-75';
                        badge.innerText = `${start} - ${end}`;
                    } else if(slot.is_booked) {
                        badge.className += ' bg-danger-subtle text-danger-emphasis border border-danger-subtle';
                        badge.innerText = `${start} - ${end} (Khách)`;
                    } else if(slot.is_locked_by_owner) {
                        badge.className += ' bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                        badge.innerText = `${start} - ${end} (Khóa)`;
                    } else {
                        // Ca Trống (Cho phép Click)
                        badge.className += ' bg-white text-dark border shadow-sm';
                        badge.style.cursor = 'pointer';
                        badge.innerText = `${start} - ${end}`;
                        
                        // SỰ KIỆN: CLICK CHỌN NHIỀU CA CÙNG LÚC
                        // SỰ KIỆN: CLICK CHỌN NHIỀU CA CÙNG LÚC
                        // SỰ KIỆN: CLICK CHỌN NHIỀU CA CÙNG LÚC (Cho phép chọn rời rạc)
                        badge.onclick = () => {
                            const slotIdx = selectedLockSlots.findIndex(s => s.start === slot.start_time);
                            if (slotIdx > -1) {
                                // Bỏ chọn
                                selectedLockSlots.splice(slotIdx, 1);
                                badge.classList.remove('bg-primary', 'text-white', 'border-primary', 'shadow');
                                badge.classList.add('bg-white', 'text-dark', 'border', 'shadow-sm');
                            } else {
                                // Chọn thêm
                                selectedLockSlots.push({ start: slot.start_time, end: slot.end_time });
                                badge.classList.remove('bg-white', 'text-dark', 'border', 'shadow-sm');
                                badge.classList.add('bg-primary', 'text-white', 'border-primary', 'shadow');
                            }
                            // Sort và gắn thẳng vào input ẩn dưới dạng JSON chuỗi
                            selectedLockSlots.sort((a, b) => a.start.localeCompare(b.start));
                            document.getElementById('selectedSlotsInput').value = JSON.stringify(selectedLockSlots);
                        };
                    }
                    vis.appendChild(badge);
                });
            } else {
                vis.innerHTML = '<span class="text-muted small m-auto">Ngày này chưa có lịch được tạo.</span>';
            }
        } catch(e) {
            vis.innerHTML = '<span class="text-danger small m-auto">Lỗi tải lịch.</span>';
        }
    }

   

    // ... (Giữ nguyên đoạn document.getElementById('btnSaveLockSlot').addEventListener... của bạn ở dưới)
    // Nút Lưu Khóa sân (Giữ nguyên logic gọi API)
    document.getElementById('btnSaveLockSlot').addEventListener('click', async function() {
        const form = document.getElementById('lockSlotForm');
        const formData = new FormData(form);
        const submitBtn = this;
        
        // FIX: Xóa sạch các thông báo lỗi cũ trước khi gửi Request mới
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.innerText = '');

        submitBtn.disabled = true; 
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';

        try {
            const response = await fetch(currentLockUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData
            });
            
            const result = await response.json();
            
            if (response.ok) {
                alert(result.message); 
                window.location.reload();
            } else if (response.status === 422) {
                // FIX: Map lỗi Validation trả về từ Backend vào thẳng các ô Input
                if(result.errors) {
                    for (const key in result.errors) {
                        const input = form.querySelector(`[name="${key}"]`);
                        if (input) {
                            input.classList.add('is-invalid');
                            document.getElementById(`error-lock-${key}`).innerText = result.errors[key][0];
                        }
                    }
                } else {
                    alert(result.message);
                }
            } else { 
                alert(result.message || 'Thao tác thất bại.'); 
            }
        } catch (error) { 
            alert('Lỗi kết nối máy chủ.'); 
        } finally { 
            submitBtn.disabled = false; 
            submitBtn.innerText = 'Thực hiện Khóa'; 
        }
    });
    // --- LOGIC MỞ KHÓA SÂN (UNLOCK) ---
    async function unlockSlot(lockId) {
        if (!confirm('Bạn có chắc chắn muốn mở khóa ca này không? Khách hàng sẽ có thể đặt sân trở lại.')) return;

        try {
            const response = await fetch(`/owner/courts/locks/${lockId}`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json', 
                    'X-CSRF-TOKEN': csrfToken 
                },
                // Gửi Request dưới dạng DELETE
                body: JSON.stringify({ _method: 'DELETE' })
            });

            const result = await response.json();
            if (response.ok) {
                alert(result.message);
                window.location.reload();
            } else {
                alert(result.message || 'Không thể mở khóa lúc này.');
            }
        } catch (err) {
            alert('Lỗi kết nối đến máy chủ.');
        }
    }
    // --- LOGIC CHÍNH SÁCH HỦY SÂN ---
    const venueId = {{ $venue->id }};
    
    async function loadPolicies() {
        try {
            const res = await fetch(`/owner/venues/${venueId}/cancellation-policies`);
            const policies = await res.json();
            const tbody = document.getElementById('policyList');
            
            if (policies.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4">Chưa có cấu hình. Khách hủy sân sẽ được miễn phí 100%.</td></tr>';
                return;
            }

            tbody.innerHTML = policies.map(p => `
                <tr>
                    <td class="py-3 px-4 fw-medium text-danger">Hủy trong vòng ${p.hours_before} giờ trước ca</td>
                    <td class="py-3 px-4 text-center"><span class="badge bg-warning text-dark fs-6">Phạt ${p.fee_percent}%</span></td>
                    <td class="py-3 px-4 text-end">
                        <button onclick="deletePolicy(${p.id})" class="btn btn-sm btn-outline-danger">Xóa</button>
                    </td>
                </tr>
            `).join('');
        } catch (e) {
            console.error('Lỗi tải chính sách');
        }
    }

    document.getElementById('formAddPolicy').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnAddPolicy');
        btn.disabled = true; btn.innerHTML = 'Đang lưu...';

        try {
            const res = await fetch(`/owner/venues/${venueId}/cancellation-policies`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({
                    hours_before: document.getElementById('pol_hours').value,
                    fee_percent: document.getElementById('pol_fee').value
                })
            });
            const data = await res.json();
            
            if (res.ok) {
                document.getElementById('pol_hours').value = '';
                document.getElementById('pol_fee').value = '';
                
                // MẸO UX: Chờ bảng tải xong rồi mới bật alert
                await loadPolicies(); 
                setTimeout(() => alert('Đã thêm chính sách thành công!'), 100);
            } else {
                alert(data.errors?.fee_percent?.[0] || data.errors?.hours_before?.[0] || data.message || 'Lỗi thêm chính sách');
            }
        } catch (err) { alert('Lỗi máy chủ'); } 
        finally { btn.disabled = false; btn.innerHTML = 'Thêm mốc'; }
    });

    // HÀM XÓA CHÍNH SÁCH CÓ THÔNG BÁO VÀ KHÔNG BỊ TRỄ
    async function deletePolicy(policyId) {
        if (!confirm('Bạn có chắc chắn muốn xóa mốc phạt này không?')) return;
        
        try {
            const res = await fetch(`/owner/venues/${venueId}/cancellation-policies/${policyId}`, {
                method: 'POST', 
                headers: { 
                    'X-CSRF-TOKEN': csrfToken, 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json' 
                },
                body: JSON.stringify({ _method: 'DELETE' }) 
            });
            
            const data = await res.json();
            
            if (res.ok) {
                // MẸO UX: Cập nhật lại giao diện trước khi bật Alert
                await loadPolicies();
                setTimeout(() => alert('Đã xóa chính sách thành công!'), 100);
            } else {
                alert(data.message || 'Lỗi không thể xóa chính sách.');
            }
        } catch (e) { 
            alert('Lỗi kết nối đến máy chủ.'); 
        }
    }

    // Khởi chạy khi load trang
    document.addEventListener('DOMContentLoaded', () => loadPolicies());
    // --- LOGIC NỘI QUY CƠ SỞ ---

    // 2. Hàm Lưu Nội quy bằng Fetch API
    document.getElementById('btnSaveRules').addEventListener('click', async function() {
        const btn = this;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang lưu...';

        try {
            const res = await fetch(`/owner/venues/${venueId}/rules`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json', 
                    'Accept': 'application/json', 
                    'X-CSRF-TOKEN': csrfToken 
                },
                body: JSON.stringify({
                    _method: 'PATCH', // Giả lập method PATCH của Laravel
                    rules: document.getElementById('venueRulesInput').value
                })
            });
            
            const data = await res.json();
            
            if (res.ok) {
                alert(data.message);
            } else {
                alert('Có lỗi xảy ra khi lưu nội quy.');
            }
        } catch (error) {
            alert('Lỗi kết nối máy chủ.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
    function toggleRulesCard() {
        const card = document.getElementById('rulesCard');
        if (card.style.display === 'none') {
            card.style.display = 'block'; // Hiện form
            card.scrollIntoView({behavior: 'smooth', block: 'center'}); // Cuộn xuống
        } else {
            card.style.display = 'none'; // Ẩn form
        }
    }

    // HÀM XÓA CHÍNH SÁCH CÓ THÔNG BÁO VÀ KHÔNG BỊ TRỄ
    async function deletePolicy(policyId) {
        if (!confirm('Bạn có chắc chắn muốn xóa mốc phạt này không?')) return;
        
        try {
            const res = await fetch(`/owner/venues/${venueId}/cancellation-policies/${policyId}`, {
                method: 'POST', 
                headers: { 
                    'X-CSRF-TOKEN': csrfToken, 
                    'Content-Type': 'application/json',
                    'Accept': 'application/json' 
                },
                body: JSON.stringify({ _method: 'DELETE' }) 
            });
            
            const data = await res.json();
            
            if (res.ok) {
                // MẸO UX: Cập nhật lại giao diện trước khi bật Alert
                await loadPolicies();
                setTimeout(() => alert('Đã xóa chính sách thành công!'), 100);
            } else {
                alert(data.message || 'Lỗi không thể xóa chính sách.');
            }
        } catch (e) { 
            alert('Lỗi kết nối đến máy chủ.'); 
        }
    }
</script>
</body>
</html>