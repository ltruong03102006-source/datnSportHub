<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm điểm sân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
    <style>
        body { background: #f4f7fb; }
        .card-shell { border: 0; border-radius: 18px; box-shadow: 0 12px 35px rgba(15, 23, 42, 0.08); }
        .preview-box { min-height: 220px; border: 2px dashed #cbd5e1; border-radius: 14px; display: flex; align-items: center; justify-content: center; background: #f8fafc; overflow: hidden; }
        .preview-box img { width: 100%; height: 100%; object-fit: cover; }
        .is-invalid { border-color: #dc3545; }
        .invalid-feedback { display: block; }
        .spinner-border-sm { width: 1rem; height: 1rem; border-width: 0.15em; }
        #map { border-radius: 12px; border: 1px solid #cbd5e1; z-index: 1; }
    </style>
</head>
<body>
<div class="container py-4 py-lg-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('owner.web.venues.index') }}">Quản lý sân</a></li>
            <li class="breadcrumb-item active" aria-current="page">Thêm điểm sân</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card card-shell">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h1 class="h3 mb-1">Tạo venue mới</h1>
                            <p class="text-muted mb-0">Tạo một điểm sân mới trước khi khai báo các sân nhỏ.</p>
                        </div>
                        <a href="{{ route('owner.web.venues.index') }}" class="btn btn-outline-secondary btn-sm">Quay lại</a>
                    </div>

                    <div id="formAlert" class="alert d-none" role="alert"></div>

                    <form id="venueForm" enctype="multipart/form-data" novalidate>
                        @csrf
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="sport_id" class="form-label">Loại môn thể thao <span class="text-danger">*</span></label>
                                <select id="sport_id" name="sport_id" class="form-select" required>
                                    <option value="">-- Chọn môn thể thao --</option>
                                    @foreach($sports as $sport)
                                        <option value="{{ $sport->id }}" {{ old('sport_id') == $sport->id ? 'selected' : '' }}>{{ $sport->name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="error-sport_id"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="name" class="form-label">Tên điểm sân <span class="text-danger">*</span></label>
                                <input id="name" name="name" type="text" class="form-control" maxlength="255" required value="{{ old('name') }}">
                                <div class="invalid-feedback" id="error-name"></div>
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label">Địa chỉ <span class="text-danger">*</span></label>
                                <input id="address" name="address" type="text" class="form-control" maxlength="500" required value="{{ old('address') }}">
                                <div class="invalid-feedback" id="error-address"></div>
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label">Mô tả</label>
                                <textarea id="description" name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                                <div class="invalid-feedback" id="error-description"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="banner" class="form-label">Banner</label>
                                <input id="banner" name="banner" type="file" class="form-control" accept="image/jpg,image/jpeg,image/png">
                                <div class="form-text">Định dạng cho phép: jpg, jpeg, png. Tối đa 2MB.</div>
                                <div class="invalid-feedback" id="error-banner"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="preview-box" id="previewBox">
                                    <span class="text-muted">Xem trước banner</span>
                                </div>
                            </div>

                            <div class="col-12 mt-4">
                                <div class="p-4 rounded-3 border border-stone-200 bg-white shadow-sm">
                                    <label for="gallery_images" class="form-label fw-bold text-emerald-700">Thư viện hình ảnh (Gallery)</label>
                                    <p class="form-text text-muted mb-3">Bạn có thể ấn giữ phím Ctrl (hoặc Cmd) để chọn nhiều ảnh cùng lúc. Giúp khách hàng có cái nhìn thực tế nhất về sân của bạn.</p>
                                    
                                    <input id="gallery_images" name="gallery_images[]" type="file" class="form-control form-control-lg" accept="image/jpg,image/jpeg,image/png" multiple>
                                    <div class="invalid-feedback" id="error-gallery_images"></div>
                                </div>
                            </div>
                            <div class="col-12 mt-3">
                                <label class="form-label fw-bold">Chọn vị trí trên bản đồ <span class="text-danger">*</span></label>
                                <div id="map" style="height: 350px;"></div>
                                <div class="form-text text-primary">💡 Kéo thả ghim hoặc dán tọa độ trực tiếp vào ô bên dưới, bản đồ sẽ tự động nhảy theo!</div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="lat" class="form-label">Vĩ độ (Latitude)</label>
                                <input id="lat" name="lat" type="number" step="any" class="form-control" value="{{ old('lat') }}" placeholder="Ví dụ: 21.028511">
                                <div class="invalid-feedback" id="error-lat"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="lng" class="form-label">Kinh độ (Longitude)</label>
                                <input id="lng" name="lng" type="number" step="any" class="form-control" value="{{ old('lng') }}" placeholder="Ví dụ: 105.804817">
                                <div class="invalid-feedback" id="error-lng"></div>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-md-row gap-2 mt-4">
                            <button id="submitBtn" type="submit" class="btn btn-primary px-4">
                                <span id="submitSpinner" class="spinner-border spinner-border-sm me-2 d-none" role="status" aria-hidden="true"></span>
                                <span id="submitText">Lưu điểm sân</span>
                            </button>
                            <button type="reset" class="btn btn-outline-secondary">Làm mới</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    // --- 1. XỬ LÝ BẢN ĐỒ THÔNG MINH ---
    const latInput = document.getElementById('lat');
    const lngInput = document.getElementById('lng');

    const defaultLat = 21.028511; 
    const defaultLng = 105.804817;

    const map = L.map('map').setView([defaultLat, defaultLng], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

    let marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

    // Cập nhật input khi thao tác trên bản đồ
    function updateInputs(lat, lng) {
        latInput.value = lat.toFixed(6);
        lngInput.value = lng.toFixed(6);
    }

    marker.on('dragend', function(e) {
        const position = marker.getLatLng();
        updateInputs(position.lat, position.lng);
    });

    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        updateInputs(e.latlng.lat, e.latlng.lng);
    });

    // Bản đồ nhảy tự động khi nhập tay vào Input
    function syncMapWithInputs() {
        let lat = parseFloat(latInput.value);
        let lng = parseFloat(lngInput.value);

        if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
            marker.setLatLng([lat, lng]);
            map.flyTo([lat, lng], 15);
        }
    }

    latInput.addEventListener('input', syncMapWithInputs);
    lngInput.addEventListener('input', syncMapWithInputs);

    // Khởi tạo tọa độ lần đầu
    if(!latInput.value) updateInputs(defaultLat, defaultLng);
    else syncMapWithInputs();

    // --- 2. XỬ LÝ FORM & SUBMIT ---
    const form = document.getElementById('venueForm');
    const alertBox = document.getElementById('formAlert');
    const submitBtn = document.getElementById('submitBtn');
    const submitSpinner = document.getElementById('submitSpinner');
    const submitText = document.getElementById('submitText');
    const previewBox = document.getElementById('previewBox');
    const bannerInput = document.getElementById('banner');

    const clearErrors = () => {
        document.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach((el) => { el.textContent = ''; });
    };

    const showAlert = (message, type = 'danger') => {
        alertBox.className = `alert alert-${type}`;
        alertBox.textContent = message;
        alertBox.classList.remove('d-none');
    };

    bannerInput.addEventListener('change', (e) => {
        const file = e.target.files && e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = (e) => { previewBox.innerHTML = `<img src="${e.target.result}" alt="Preview banner">`; };
        reader.readAsDataURL(file);
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearErrors();
        submitBtn.disabled = true;
        submitSpinner.classList.remove('d-none');
        submitText.textContent = 'Đang lưu...';
        alertBox.classList.add('d-none');

        try {
            const response = await fetch('{{ route('owner.web.venues.store') }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: new FormData(form)
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                if (response.status === 422 && data.errors) {
                    Object.entries(data.errors).forEach(([field, messages]) => {
                        const fieldEl = document.getElementById(field);
                        const errorEl = document.getElementById(`error-${field}`);
                        if (fieldEl) fieldEl.classList.add('is-invalid');
                        if (errorEl) errorEl.textContent = messages[0];
                    });
                    showAlert('Vui lòng kiểm tra lại thông tin.', 'warning');
                    return;
                }
                showAlert(data.message || 'Lỗi hệ thống.', 'danger');
                return;
            }
            window.location.href = '{{ route('owner.web.venues.index') }}?created=1';
        } catch (error) {
            showAlert('Đã xảy ra lỗi khi kết nối máy chủ.', 'danger');
        } finally {
            submitBtn.disabled = false;
            submitSpinner.classList.add('d-none');
            submitText.textContent = 'Lưu điểm sân';
        }
    });
</script>
</body>
</html>