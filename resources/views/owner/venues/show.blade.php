<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết điểm sân: {{ $venue->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <style>
        body { background: #f4f7fb; }
        .card-shell { border: 0; border-radius: 18px; box-shadow: 0 12px 35px rgba(15, 23, 42, 0.08); }
        #map-view { border-radius: 12px; border: 1px solid #cbd5e1; z-index: 1; }
    </style>
</head>
<body>
<div class="container py-4 py-lg-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('owner.web.venues.index') }}">Quản lý sân</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $venue->name }}</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                {{ $venue->name }}
                @if($venue->status === 'active')
                    <span class="badge bg-success ms-2 fs-6 align-middle">Đang hoạt động</span>
                @elseif($venue->status === 'inactive')
                    <span class="badge bg-secondary ms-2 fs-6 align-middle">Tạm ngừng</span>
                @else
                    <span class="badge bg-warning text-dark ms-2 fs-6 align-middle">Chờ duyệt</span>
                @endif
            </h1>
            <p class="text-muted mb-0">Thông tin chi tiết điểm sân và quản lý các sân con.</p>
        </div>
        <div>
            <a href="{{ route('owner.web.venues.edit', $venue->id) }}" class="btn btn-outline-primary btn-sm me-2">Sửa thông tin</a>
            <a href="{{ route('owner.web.venues.index') }}" class="btn btn-outline-secondary btn-sm">Quay lại</a>
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
            <h5 class="mb-0 fw-bold">Danh sách Sân con</h5>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createCourtModal">
                + Tạo Sân con
            </button>
        </div>
        <div class="card-body p-0">
            <div class="list-group list-group-flush rounded-bottom" style="border-bottom-left-radius: 18px; border-bottom-right-radius: 18px;">
                @forelse($venue->courts as $court)
                    <div class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-md-center py-3 px-4">
                        <div class="mb-3 mb-md-0">
                            <h6 class="mb-1 fw-bold text-dark fs-5">{{ $court->name }}</h6>
                            <div class="d-flex flex-wrap gap-2 align-items-center mt-2">
                                <span class="text-muted small">Trạng thái:</span>
                                @if($court->status === 'active')
                                    <span class="badge bg-success">Hoạt động</span>
                                @else
                                    <span class="badge bg-secondary">Bảo trì</span>
                                @endif
                                
                                <span class="text-muted small ms-2">Tổng ca: <strong class="text-dark">{{ $court->timeSlots->count() ?? 0 }}</strong></span>
                            </div>
                        </div>
                        
                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-sm btn-outline-primary" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#generateSlotsModal"
                                    onclick="setGenerateCourtId({{ $court->id }}, '{{ $court->name }}')">
                                Sinh ca tự động
                            </button>

                            <button class="btn btn-sm btn-outline-success" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#manualSlotModal"
                                    onclick="setManualCourtId({{ $court->id }}, '{{ $court->name }}')">
                                + Thêm ca lẻ
                            </button>
                            
                            <button class="btn btn-sm btn-outline-secondary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#editCourtModal"
                                    data-id="{{ $court->id }}"
                                    data-name="{{ $court->name }}"
                                    data-status="{{ $court->status }}"
                                    onclick="populateCourtEditModal(this)">
                                Sửa
                            </button>

                            <button class="btn btn-sm btn-outline-danger" onclick="deleteCourt({{ $court->id }}, '{{ $court->name }}')">
                                Xóa
                            </button>
                        </div>
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
</script>
</body>
</html>