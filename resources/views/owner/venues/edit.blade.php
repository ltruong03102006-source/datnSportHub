<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sửa điểm sân: {{ $venue->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <style>
        body { background: #f4f7fb; font-family: Inter, system-ui, sans-serif; }
        .card-shell { border: 0; border-radius: 18px; box-shadow: 0 12px 35px rgba(15, 23, 42, 0.08); overflow: hidden; }
        .preview-box { min-height: 220px; border: 2px dashed #cbd5e1; border-radius: 14px; display: flex; align-items: center; justify-content: center; background: #f8fafc; overflow: hidden; }
        .preview-box img { width: 100%; height: 100%; object-fit: cover; }
        .is-invalid { border-color: #dc3545; }
        .invalid-feedback { display: block; font-weight: 500; }
        #map { border-radius: 12px; border: 1px solid #cbd5e1; z-index: 1; }
        .form-label { font-weight: 600; color: #334155; font-size: 0.9rem; }
        .form-control, .form-select { border-radius: 8px; padding: 0.6rem 1rem; }
        .bg-gray-50 { background-color: #f8fafc; }
        
        /* Tab Styles */
        .custom-tabs { gap: 10px; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 25px; }
        .custom-tabs .nav-link { border-radius: 10px; color: #64748b; font-weight: 600; padding: 10px 20px; transition: 0.3s; background: transparent; }
        .custom-tabs .nav-link:hover { background: #f1f5f9; color: #0f172a; }
        .custom-tabs .nav-link.active { background: #059669; color: white; box-shadow: 0 4px 10px rgba(5, 150, 105, 0.2); }

        /* Lock Overlay cho Tab Pháp Lý */
        .tab-locked-wrapper { position: relative; }
        .locked-overlay {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(3px);
            z-index: 10; border-radius: 12px; display: flex; align-items: center; justify-content: center;
            cursor: not-allowed;
        }
        .locked-message {
            background: #fffbeb; border: 1px solid #fcd34d; padding: 20px 30px;
            border-radius: 12px; text-align: center; color: #b45309; font-weight: 600; box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

@php
    // Kiểm tra xem cơ sở có đang chờ duyệt hồ sơ pháp lý không
    $hasPendingUpdate = $venue->pendingUpdateRequest()->exists();
    
    // 1. Lấy ra yêu cầu thay đổi GẦN NHẤT (Bất kể trạng thái là chờ duyệt, từ chối hay đã duyệt)
    $latestRequest = \App\Models\VenueUpdateRequest::where('venue_id', $venue->id)
                                ->latest()
                                ->first();
                                
    // 2. CHỈ hiện lỗi khi và chỉ khi cái yêu cầu GẦN NHẤT ĐÓ có trạng thái là 'rejected'
    $latestRejectedRequest = ($latestRequest && $latestRequest->status === 'rejected') ? $latestRequest : null;
@endphp

<div class="container py-4 py-lg-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-success">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('owner.web.venues.index') }}" class="text-decoration-none text-success">Quản lý sân</a></li>
            <li class="breadcrumb-item active" aria-current="page">Sửa điểm sân</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <div class="card card-shell mb-5">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h1 class="h3 mb-1 fw-bold text-dark">Cập nhật thông tin</h1>
                            <p class="text-muted mb-0">Sửa điểm sân: <span class="fw-bold text-dark">{{ $venue->name }}</span></p>
                        </div>
                        <a href="{{ route('owner.web.venues.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-bold">Quay lại</a>
                    </div>

                    <div id="formAlert" class="alert d-none" role="alert"></div>

                    <!-- ĐIỀU HƯỚNG TABS -->
                    <ul class="nav nav-pills custom-tabs" id="venueTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="basic-tab" data-bs-toggle="pill" data-bs-target="#basic" type="button" role="tab">
                                <i class="fa-solid fa-circle-info me-2"></i>1. Thông tin cơ bản
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="media-tab" data-bs-toggle="pill" data-bs-target="#media" type="button" role="tab">
                                <i class="fa-solid fa-images me-2"></i>2. Hình ảnh & Bản đồ
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="legal-tab" data-bs-toggle="pill" data-bs-target="#legal" type="button" role="tab">
                                <i class="fa-solid fa-shield-halved me-2"></i>3. Hồ sơ pháp lý
                                @if($hasPendingUpdate)
                                    <span class="badge bg-warning text-dark ms-2"><i class="fa-solid fa-lock me-1"></i>Đang chờ duyệt</span>
                                @endif
                            </button>
                        </li>
                    </ul>

                    <form id="venueForm" enctype="multipart/form-data" novalidate>
                        @csrf
                        @method('PUT') 
                        
                        <div class="tab-content" id="venueTabsContent">
                            
                            <!-- TAB 1: THÔNG TIN CƠ BẢN -->
                            <div class="tab-pane fade show active" id="basic" role="tabpanel">
                                <div class="row g-3 mb-4">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Loại môn thể thao <span class="text-danger">*</span></label>
                                        <select id="sport_id" name="sport_id" class="form-select" required oninput="this.classList.remove('is-invalid')">
                                            <option value="">-- Chọn môn thể thao --</option>
                                            @foreach($sports as $sport)
                                                <option value="{{ $sport->id }}" {{ old('sport_id', $venue->sport_id) == $sport->id ? 'selected' : '' }}>{{ $sport->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Tên điểm sân <span class="text-danger">*</span></label>
                                        <input name="name" type="text" class="form-control" maxlength="255" required value="{{ old('name', $venue->name) }}" oninput="this.classList.remove('is-invalid')">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Số điện thoại hotline <span class="text-danger">*</span></label>
                                        <input name="phone" type="text" class="form-control" 
                                               oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.classList.remove('is-invalid');" 
                                               maxlength="20" value="{{ old('phone', $venue->phone ?? '') }}" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Email liên hệ <span class="text-danger">*</span></label>
                                        <input name="email" type="email" class="form-control" value="{{ old('email', $venue->email ?? '') }}" oninput="this.classList.remove('is-invalid')" required>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Mô tả cơ sở</label>
                                        <textarea name="description" class="form-control" rows="4">{{ old('description', $venue->description) }}</textarea>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 2: HÌNH ẢNH & BẢN ĐỒ -->
                            <div class="tab-pane fade" id="media" role="tabpanel">
                                <div class="row g-3 mb-4 border-bottom pb-4">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Banner chính (Để trống nếu không đổi)</label>
                                        <input id="banner" name="banner" type="file" class="form-control" accept="image/jpg,image/jpeg,image/png">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="preview-box" id="previewBox">
                                            @if($venue->banner)
                                                <img src="{{ asset('storage/' . $venue->banner) }}" alt="Current Banner">
                                            @else
                                                <span class="text-muted">Chưa có ảnh</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <div class="p-4 rounded-3 border border-stone-200 bg-white shadow-sm">
                                            <label class="form-label text-emerald-700">Thư viện hình ảnh (Gallery)</label>
                                            <div id="deletedImagesContainer"></div>
                                            @if($venue->images && $venue->images->count() > 0)
                                                <div class="row g-2 mb-3 pb-3 border-bottom">
                                                    @foreach($venue->images as $img)
                                                        <div class="col-4 col-md-3 col-lg-2 position-relative" id="img-box-{{ $img->id }}">
                                                            <img src="{{ asset('storage/' . $img->image_path) }}" class="img-thumbnail w-100" style="height: 100px; object-fit: cover;">
                                                            <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 py-0 px-2 rounded-circle" onclick="markAsDeleted({{ $img->id }})">×</button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <input name="gallery_images[]" type="file" class="form-control" accept="image/jpg,image/jpeg,image/png" multiple>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                                        <select id="province_code" name="province_code" class="form-select" data-current-ward="{{ old('ward_code', $venue->ward_code) }}">
                                            <option value="">-- Chọn tỉnh/thành --</option>
                                            @foreach ($provinces as $province)
                                                <option value="{{ $province->code }}" @selected(old('province_code', $venue->province_code) == $province->code)>{{ $province->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label">Phường/Xã <span class="text-danger">*</span></label>
                                        <select id="ward_code" name="ward_code" class="form-select" disabled>
                                            <option value="">-- Phường/Xã --</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Địa chỉ chi tiết <span class="text-danger">*</span></label>
                                        <input name="address" type="text" class="form-control" maxlength="500" required value="{{ old('address', $venue->address) }}">
                                        <div class="invalid-feedback"></div>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <label class="form-label">Kéo thả ghim để chọn vị trí chính xác</label>
                                        <div id="map" style="height: 350px;"></div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <input id="lat" name="lat" type="hidden" value="{{ old('lat', $venue->lat) }}">
                                        <input id="lng" name="lng" type="hidden" value="{{ old('lng', $venue->lng) }}">
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 3: HỒ SƠ PHÁP LÝ (BỊ KHÓA NẾU ĐANG CHỜ DUYỆT) -->
                            <div class="tab-pane fade" id="legal" role="tabpanel">
                                <div class="tab-locked-wrapper p-4 bg-gray-50 border rounded-4">
                                    
                                    {{-- LỚP PHỦ KHÓA (OVERLAY) HIỆN RA NẾU CÓ BẢN NHÁP --}}
                                    @if($hasPendingUpdate)
                                        <div class="locked-overlay">
                                            <div class="locked-message">
                                                <i class="fa-solid fa-lock-open fa-shake fs-1 mb-3 text-warning"></i><br>
                                                Hồ sơ pháp lý đang được Admin duyệt.<br>
                                                <span class="text-muted fw-normal" style="font-size: 13px;">Bạn không thể chỉnh sửa khu vực này cho đến khi có kết quả.</span>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="alert alert-warning mb-4" style="border-radius: 12px;">
                                        <strong>⚠️ Lưu ý quan trọng:</strong> Bất kỳ sự thay đổi nào ở Tab này đều sẽ được đưa vào trạng thái <strong>"Chờ duyệt"</strong> và không áp dụng ngay.
                                    </div>
                                    {{-- HIỂN THỊ LÝ DO TỪ CHỐI CỦA ADMIN (NẾU CÓ) --}}
                                    @if($latestRejectedRequest && !$hasPendingUpdate)
                                        <div class="alert alert-danger mb-4 shadow-sm" style="border-radius: 12px; border: 1px solid #fca5a5;">
                                            <h5 class="alert-heading fw-bold mb-2 text-danger">
                                                <i class="fa-solid fa-triangle-exclamation me-2"></i>Yêu cầu cập nhật bị từ chối!
                                            </h5>
                                            <p class="mb-2 text-danger-emphasis">
                                                Yêu cầu thay đổi hồ sơ pháp lý gần nhất của bạn (gửi lúc {{ $latestRejectedRequest->created_at->format('H:i d/m/Y') }}) đã bị Admin từ chối với lý do:
                                            </p>
                                            <div class="p-3 bg-white rounded border border-danger text-danger fw-bold" style="font-size: 15px;">
                                                <i class="fa-solid fa-quote-left me-2 opacity-50"></i>
                                                {{ $latestRejectedRequest->admin_note }}
                                            </div>
                                            <p class="mb-0 mt-2 small text-danger-emphasis">
                                                <em>* Vui lòng đọc kỹ lý do, chỉnh sửa lại thông tin bên dưới và bấm "Lưu toàn bộ thay đổi" để gửi lại yêu cầu mới.</em>
                                            </p>
                                        </div>
                                    @endif
                                    <div class="row g-3">
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">Tên chủ sở hữu</label>
                                            <input type="text" name="owner_name" class="form-control" value="{{ old('owner_name', $venue->legalDocument->owner_name ?? '') }}">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">Số CCCD <span class="text-danger">*</span></label>
                                            <input type="text" name="citizen_id" class="form-control" 
                                                   oninput="this.value = this.value.replace(/[^0-9]/g, ''); this.classList.remove('is-invalid');" 
                                                   minlength="12" maxlength="12" pattern="\d{12}"
                                                   value="{{ old('citizen_id', $venue->legalDocument->citizen_id ?? '') }}" required>
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">Mã số thuế / GPKD</label>
                                            <input type="text" name="business_license_number" class="form-control" 
                                                   oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')"
                                                   value="{{ old('business_license_number', $venue->legalDocument->business_license_number ?? '') }}">
                                            <div class="invalid-feedback"></div>
                                        </div>
                                        
                                        <div class="col-12"><hr class="text-muted my-2"></div>

                                        <div class="col-12 col-md-4">
                                            <label class="form-label">Tên ngân hàng</label>
                                            <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name', $venue->legalDocument->bank_name ?? '') }}">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">Số tài khoản</label>
                                            <input type="text" name="bank_account_number" class="form-control" value="{{ old('bank_account_number', $venue->legalDocument->bank_account_number ?? '') }}">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">Tên chủ tài khoản</label>
                                            <input type="text" name="bank_account_holder" class="form-control" value="{{ old('bank_account_holder', $venue->legalDocument->bank_account_holder ?? '') }}">
                                        </div>

                                        <div class="col-12"><hr class="text-muted my-2"></div>

                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Cập nhật CCCD Mặt trước</label>
                                            <input type="file" name="citizen_front_image" class="form-control" accept="image/*">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label">Cập nhật CCCD Mặt sau</label>
                                            <input type="file" name="citizen_back_image" class="form-control" accept="image/*">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">Cập nhật Giấy phép KD</label>
                                            <input type="file" name="business_license_file" class="form-control" accept=".pdf,image/*">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">Cập nhật Hợp đồng thuê</label>
                                            <input type="file" name="rental_contract_file" class="form-control" accept=".pdf,image/*">
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label class="form-label">Cập nhật Sổ đỏ/Sổ hồng</label>
                                            <input type="file" name="land_certificate_file" class="form-control" accept=".pdf,image/*">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- Kết thúc Tab Content -->

                        <!-- SUBMIT BUTTON -->
                        <div class="d-flex justify-content-end gap-3 mt-5 pt-4 border-top">
                            <a href="{{ route('owner.web.venues.index') }}" class="btn btn-light px-4 fw-bold">Hủy bỏ</a>
                            <button id="submitBtn" type="submit" class="btn btn-success px-5 fw-bold" style="background-color: #059669; border-color: #047857;">
                                <span id="submitSpinner" class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                                <span id="submitText">Lưu toàn bộ thay đổi</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    // Xử lý Bản đồ, File, Select (Giữ nguyên logic javascript cũ của bạn)
    const latInput = document.getElementById('lat');
    const lngInput = document.getElementById('lng');
    const initialLat = {{ old('lat', $venue->lat ?? '21.028511') }}; 
    const initialLng = {{ old('lng', $venue->lng ?? '105.804817') }};
    const map = L.map('map').setView([initialLat, initialLng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);
    let marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);
    
    // Fix lỗi bản đồ không load đúng size khi nằm trong Tab bị ẩn
    document.getElementById('media-tab').addEventListener('shown.bs.tab', function (e) {
        setTimeout(() => map.invalidateSize(), 100);
    });

    function updateInputs(lat, lng) {
        latInput.value = lat.toFixed(6);
        lngInput.value = lng.toFixed(6);
    }
    marker.on('dragend', function(e) { updateInputs(marker.getLatLng().lat, marker.getLatLng().lng); });
    map.on('click', function(e) { marker.setLatLng(e.latlng); updateInputs(e.latlng.lat, e.latlng.lng); });

    const previewBox = document.getElementById('previewBox');
    document.getElementById('banner').addEventListener('change', (e) => {
        const file = e.target.files && e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => { previewBox.innerHTML = `<img src="${e.target.result}" alt="Preview banner">`; };
        reader.readAsDataURL(file);
    });

    function markAsDeleted(imageId) {
        document.getElementById(`img-box-${imageId}`).style.display = 'none';
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = 'deleted_image_ids[]'; input.value = imageId;
        document.getElementById('deletedImagesContainer').appendChild(input);
    }

    const provinceEl = document.getElementById('province_code');
    const wardEl = document.getElementById('ward_code');
    const provinceTS = new TomSelect(provinceEl, { searchField: 'text', placeholder: 'Tìm tỉnh/thành…', maxOptions: null });
    const wardTS = new TomSelect(wardEl, { searchField: 'text', placeholder: 'Tìm phường/xã…', maxOptions: null });

    async function loadWards(provinceCode, selectedWard = '') {
        wardTS.clear(true); wardTS.clearOptions(); wardTS.disable();
        if (!provinceCode) return;
        try {
            const res = await fetch(`/api/provinces/${provinceCode}/wards`);
            const json = await res.json();
            wardTS.addOptions(json.data.map((w) => ({ value: w.code, text: w.name })));
            wardTS.enable();
            if (selectedWard) wardTS.setValue(selectedWard, true);
        } catch (err) { wardTS.disable(); }
    }
    provinceTS.on('change', (value) => loadWards(value));
    if (provinceEl.value) loadWards(provinceEl.value, provinceEl.dataset.currentWard || '');

    // Xử lý form Submit
    const form = document.getElementById('venueForm');
    const alertBox = document.getElementById('formAlert');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        submitBtn.disabled = true;
        document.getElementById('submitSpinner').classList.remove('d-none');
        document.getElementById('submitText').textContent = 'Đang xử lý...';
        alertBox.classList.add('d-none');
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

        try {
            const response = await fetch('{{ route('owner.web.venues.update', $venue->id) }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: new FormData(form)
            });
            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                if (response.status === 422 && data.errors) {
                    Object.keys(data.errors).forEach(key => {
                        let inputElement = form.querySelector(`[name="${key}"]`);
                        if (inputElement) {
                            inputElement.classList.add('is-invalid');
                            let feedbackDiv = inputElement.parentElement.querySelector('.invalid-feedback');
                            if (feedbackDiv) feedbackDiv.textContent = data.errors[key][0];
                            
                            // Auto chuyển sang Tab chứa ô bị lỗi
                            let tabPane = inputElement.closest('.tab-pane');
                            if(tabPane) {
                                let tabId = tabPane.getAttribute('id');
                                let tabButton = document.querySelector(`[data-bs-target="#${tabId}"]`);
                                if(tabButton) {
                                    let bsTab = new bootstrap.Tab(tabButton);
                                    bsTab.show();
                                }
                            }
                        }
                    });
                    
                    setTimeout(() => {
                        let firstInvalid = form.querySelector('.is-invalid');
                        if (firstInvalid) firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 300);
                    return;
                }
                alertBox.className = 'alert alert-danger'; alertBox.innerHTML = data.message || 'Lỗi hệ thống.';
                alertBox.classList.remove('d-none'); window.scrollTo(0,0);
                return;
            }
           // Thành công thì chuyển hướng kèm theo "tín hiệu" từ Backend
            window.location.href = `{{ route('owner.web.venues.index') }}?updated=${data.update_type}`;
        } catch (error) {
            alertBox.className = 'alert alert-danger'; alertBox.innerHTML = 'Đã xảy ra lỗi mạng.';
            alertBox.classList.remove('d-none'); window.scrollTo(0,0);
        } finally {
            submitBtn.disabled = false;
            document.getElementById('submitSpinner').classList.add('d-none');
            document.getElementById('submitText').textContent = 'Lưu toàn bộ thay đổi';
        }
    });
</script>
</body>
</html>