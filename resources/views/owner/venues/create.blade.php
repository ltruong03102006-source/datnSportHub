<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo cơ sở mới</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <style>
        :root { --brand: #059669; --brand-dark: #047857; --ink: #172033; --muted: #64748b; }
        body { min-height: 100vh; background: radial-gradient(circle at top left, #d1fae5 0, transparent 32%), #f8fafc; color: var(--ink); font-family: Inter, system-ui, sans-serif; }
        .container { max-width: 1180px; }
        .breadcrumb { font-size: 13px; font-weight: 600; }
        .breadcrumb a { color: var(--brand); text-decoration: none; }
        .card-shell { overflow: hidden; border: 1px solid #e2e8f0; border-radius: 24px; box-shadow: 0 22px 60px rgba(15, 23, 42, 0.10); }
        .card-shell .card-body { padding: 2.25rem !important; }
        .card-shell h1 { color: var(--ink); font-size: 30px; font-weight: 800; letter-spacing: -.5px; }
        .card-shell .text-muted { color: var(--muted) !important; }
        .step-pill { width: 38px; height: 38px; border: 1px solid #dbe4ee; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; background: #fff; color: #94a3b8; font-weight: 800; transition: .2s; }
        .step-pill.active { border-color: var(--brand); background: var(--brand); color: #fff; box-shadow: 0 6px 14px rgba(5, 150, 105, .22); }
        .step-pill.done { border-color: #a7f3d0; background: #ecfdf5; color: var(--brand); }
        .step-pill + span { font-size: 13px; color: #64748b; }
        .step-panel { display: none; }
        .step-panel.active { display: block; }
        .step-panel.active { animation: reveal .28s ease both; }
        @keyframes reveal { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
        .form-label { color: #334155; font-size: 13px; font-weight: 700; margin-bottom: 8px; }
        .form-control, .form-select { min-height: 46px; border-color: #dbe4ee; border-radius: 10px; padding: 10px 13px; font-size: 14px; box-shadow: none; }
        textarea.form-control { min-height: 110px; }
        .form-control:focus, .form-select:focus { border-color: var(--brand); box-shadow: 0 0 0 4px rgba(5,150,105,.12); }
        .preview-box { min-height: 240px; border: 2px dashed #a7f3d0; border-radius: 16px; display: flex; align-items: center; justify-content: center; background: #f0fdf4; overflow: hidden; }
        .preview-box img { width: 100%; height: 100%; object-fit: cover; }
        #map { height: 360px; border-radius: 16px; border: 1px solid #dbe4ee; }
        .btn { min-height: 42px; border-radius: 10px; padding: 10px 16px; font-size: 13px; font-weight: 700; }
        .btn-primary { border-color: var(--brand); background: var(--brand); }
        .btn-primary:hover, .btn-success:hover { border-color: var(--brand-dark); background: var(--brand-dark); }
        .btn-success { border-color: var(--brand); background: var(--brand); }
        .btn-outline-secondary { color: #475569; border-color: #cbd5e1; }
        .d-flex.justify-content-between.mt-4 { margin-top: 32px !important; padding-top: 22px; border-top: 1px solid #edf2f7; }
        @media (max-width: 767px) { .card-shell .card-body { padding: 1.4rem !important; } .d-flex.flex-wrap.gap-2.mb-4 { gap: 10px !important; } .d-flex.flex-wrap.gap-2.mb-4 > .text-muted { display: none; } }
    </style>
</head>
<body>
<div class="container py-4 py-lg-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('owner.web.venues.index') }}">Quản lý sân</a></li>
            <li class="breadcrumb-item active">Tạo cơ sở mới</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-9">
            <div class="card card-shell">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h1 class="h3 mb-1">Tạo cơ sở mới</h1>
                            <p class="text-muted mb-0">Hoàn tất 4 bước để gửi yêu cầu duyệt cơ sở.</p>
                        </div>
                        <a href="{{ route('owner.web.venues.index') }}" class="btn btn-outline-secondary btn-sm">Quay lại</a>
                    </div>

                    <div id="formAlert" class="alert d-none" role="alert"></div>

                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <div class="d-flex align-items-center gap-2"><span class="step-pill active" data-step="1">1</span><span class="fw-semibold">Thông tin</span></div>
                        <div class="text-muted">›</div>
                        <div class="d-flex align-items-center gap-2"><span class="step-pill" data-step="2">2</span><span>Liên hệ & bản đồ</span></div>
                        <div class="text-muted">›</div>
                        <div class="d-flex align-items-center gap-2"><span class="step-pill" data-step="3">3</span><span>Ảnh</span></div>
                        <div class="text-muted">›</div>
                        <div class="d-flex align-items-center gap-2"><span class="step-pill" data-step="4">4</span><span>Hồ sơ pháp lý</span></div>
                    </div>

                    <form action="{{ route('owner.web.venues.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="step-panel active" data-panel="1">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Loại môn thể thao <span class="text-danger">*</span></label>
                                    <select name="sport_id" class="form-select" required>
                                        <option value="">-- Chọn môn thể thao --</option>
                                        @foreach($sports as $sport)
                                            <option value="{{ $sport->id }}" {{ old('sport_id') == $sport->id ? 'selected' : '' }}>{{ $sport->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Tên cơ sở <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" maxlength="255" value="{{ old('name') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Tỉnh/Thành phố <span class="text-danger">*</span></label>
                                    <select name="province_code" id="province_code" class="form-select"
                                            data-old-ward="{{ old('ward_code') }}">
                                        <option value="">-- Chọn tỉnh/thành --</option>
                                        @foreach ($provinces as $province)
                                            <option value="{{ $province->code }}" @selected(old('province_code') == $province->code)>{{ $province->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('province_code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phường/Xã <span class="text-danger">*</span></label>
                                    <select name="ward_code" id="ward_code" class="form-select" disabled>
                                        <option value="">-- Phường/Xã --</option>
                                    </select>
                                    @error('ward_code')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                                    <input type="text" name="address" class="form-control" maxlength="500" value="{{ old('address') }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Mô tả</label>
                                    <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="step-panel" data-panel="2">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                                </div>
                                {{-- <div class="col-12 col-md-6">
                                    <label class="form-label">Giờ mở cửa <span class="text-danger">*</span></label>
                                    <input type="time" name="open_hours" class="form-control" value="{{ old('open_hours') }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Giờ đóng cửa <span class="text-danger">*</span></label>
                                    <input type="time" name="close_hours" class="form-control" value="{{ old('close_hours') }}" required>
                                </div> --}}
                                <div class="col-12">
                                    <label class="form-label">Địa chỉ Google Maps <span class="text-danger">*</span></label>
                                    <input type="text" name="google_maps_address" class="form-control" value="{{ old('google_maps_address') }}" required>
                                </div>
                                <div class="col-12 mt-2">
                                    <label class="form-label fw-semibold">Chọn vị trí trên bản đồ</label>
                                    <div id="map"></div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Vĩ độ</label>
                                    <input type="number" step="any" name="lat" id="lat" class="form-control" value="{{ old('lat') }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Kinh độ</label>
                                    <input type="number" step="any" name="lng" id="lng" class="form-control" value="{{ old('lng') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="step-panel" data-panel="3">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Banner chính <span class="text-danger">*</span></label>
                                    <input type="file" name="banner" id="banner" class="form-control" accept="image/*" required>
                                    <div class="form-text">jpg, jpeg, png, tối đa 2MB</div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="preview-box" id="previewBox">
                                        <span class="text-muted">Xem trước banner</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Thư viện ảnh</label>
                                    <input type="file" name="gallery_images[]" class="form-control" multiple accept="image/*">
                                </div>
                            </div>
                        </div>

                        <div class="step-panel" data-panel="4">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Tên chủ sở hữu <span class="text-danger">*</span></label>
                                    <input type="text" name="owner_name" class="form-control" value="{{ old('owner_name') }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Số CCCD <span class="text-danger">*</span></label>
                                    <input type="text" name="citizen_id" class="form-control" value="{{ old('citizen_id') }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Số giấy phép kinh doanh <span class="text-danger">*</span></label>
                                    <input type="text" name="business_license_number" class="form-control" value="{{ old('business_license_number') }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Tên ngân hàng <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Số tài khoản ngân hàng <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_account_number" class="form-control" value="{{ old('bank_account_number') }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Chủ tài khoản <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_account_holder" class="form-control" value="{{ old('bank_account_holder') }}" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">CCCD mặt trước <span class="text-danger">*</span></label>
                                    <input type="file" name="citizen_front_image" class="form-control" accept="image/*" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">CCCD mặt sau <span class="text-danger">*</span></label>
                                    <input type="file" name="citizen_back_image" class="form-control" accept="image/*" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Giấy phép kinh doanh <span class="text-danger">*</span></label>
                                    <input type="file" name="business_license_file" class="form-control" accept=".pdf,image/*" required>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label class="form-label">Hợp đồng thuê mặt bằng</label>
                                    <input type="file" name="rental_contract_file" class="form-control" accept=".pdf,image/*">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Giấy chứng nhận quyền sử dụng đất</label>
                                    <input type="file" name="land_certificate_file" class="form-control" accept=".pdf,image/*">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-outline-secondary" id="prevBtn" disabled>Quay lại</button>
                            <button type="button" class="btn btn-primary" id="nextBtn">Tiếp theo</button>
                            <button type="submit" class="btn btn-success d-none" id="submitBtn">Gửi yêu cầu duyệt</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    const steps = document.querySelectorAll('.step-panel');
    const stepPills = document.querySelectorAll('.step-pill');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const form = document.querySelector('form');
    const formAlert = document.getElementById('formAlert');
    let currentStep = 1;

    function updateStep() {
        steps.forEach(panel => {
            panel.classList.toggle('active', Number(panel.dataset.panel) === currentStep);
        });
        stepPills.forEach(pill => {
            const step = Number(pill.dataset.step);
            pill.classList.toggle('active', step === currentStep);
            pill.classList.toggle('done', step < currentStep);
        });
        prevBtn.disabled = currentStep === 1;
        nextBtn.classList.toggle('d-none', currentStep === 4);
        submitBtn.classList.toggle('d-none', currentStep !== 4);
    }

    function showAlert(message, type = 'danger') {
        formAlert.className = `alert alert-${type}`;
        formAlert.textContent = message;
        formAlert.classList.remove('d-none');
    }

    form.addEventListener('submit', function (e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            form.reportValidity();
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Đang gửi...';
    });

    prevBtn.addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep--;
            updateStep();
        }
    });

    nextBtn.addEventListener('click', () => {
    if (currentStep < 4) {
        currentStep++;
        updateStep();

        if (currentStep === 2) {
            setTimeout(() => {
                map.invalidateSize();
            }, 300);
        }
    }
});

    updateStep();

    const bannerInput = document.getElementById('banner');
    const previewBox = document.getElementById('previewBox');
    bannerInput.addEventListener('change', function () {
        const file = this.files && this.files[0];
        if (!file) {
            previewBox.innerHTML = '<span class="text-muted">Xem trước banner</span>';
            return;
        }
        const reader = new FileReader();
        reader.onload = function (e) {
            previewBox.innerHTML = `<img src="${e.target.result}" alt="Preview banner">`;
        };
        reader.readAsDataURL(file);
    });

    const latInput = document.getElementById('lat');
    const lngInput = document.getElementById('lng');
    const defaultLat = 21.028511;
    const defaultLng = 105.804817;

const map = L.map('map').setView([defaultLat, defaultLng], 14);

L.tileLayer(
    'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
    {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }
).addTo(map);
    let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

    function updateInputs(lat, lng) {
        latInput.value = lat.toFixed(6);
        lngInput.value = lng.toFixed(6);
    }

    marker.on('dragend', function () {
        const pos = marker.getLatLng();
        updateInputs(pos.lat, pos.lng);
    });
    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        updateInputs(e.latlng.lat, e.latlng.lng);
    });

    function syncMap() {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);
        if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            marker.setLatLng([lat, lng]);
            map.flyTo([lat, lng], 15);
        }
    }

    latInput.addEventListener('input', syncMap);
    lngInput.addEventListener('input', syncMap);

    if (!latInput.value || !lngInput.value) {
        updateInputs(defaultLat, defaultLng);
    } else {
        syncMap();
    }
    window.addEventListener('load', () => {
    setTimeout(() => {
        map.invalidateSize();
    }, 500);
});

    // Cascading Tỉnh -> Phường/Xã (Tom Select: có ô tìm kiếm)
    const provinceEl = document.getElementById('province_code');
    const wardEl = document.getElementById('ward_code');

    const provinceTS = new TomSelect(provinceEl, {
        searchField: 'text',
        placeholder: 'Tìm tỉnh/thành…',
        maxOptions: null,
    });
    const wardTS = new TomSelect(wardEl, {
        searchField: 'text',
        placeholder: 'Tìm phường/xã…',
        maxOptions: null,
    });

    async function loadWards(provinceCode, selectedWard = '') {
        wardTS.clear(true);
        wardTS.clearOptions();
        wardTS.disable();

        if (!provinceCode) {
            return;
        }

        try {
            const res = await fetch(`/api/provinces/${provinceCode}/wards`);
            const json = await res.json();
            wardTS.addOptions(json.data.map((w) => ({ value: w.code, text: w.name })));
            wardTS.enable();
            if (selectedWard) {
                wardTS.setValue(selectedWard, true);
            }
        } catch (err) {
            wardTS.disable();
        }
    }

    provinceTS.on('change', (value) => loadWards(value));

    // Repopulate wards after a validation error (old input)
    if (provinceEl.value) {
        loadWards(provinceEl.value, provinceEl.dataset.oldWard || '');
    }
</script>
</body>
</html>
