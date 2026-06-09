<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm điểm sân</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7fb; }
        .card-shell { border: 0; border-radius: 18px; box-shadow: 0 12px 35px rgba(15, 23, 42, 0.08); }
        .preview-box { min-height: 220px; border: 2px dashed #cbd5e1; border-radius: 14px; display: flex; align-items: center; justify-content: center; background: #f8fafc; overflow: hidden; }
        .preview-box img { width: 100%; height: 100%; object-fit: cover; }
        .is-invalid { border-color: #dc3545; }
        .invalid-feedback { display: block; }
        .spinner-border-sm { width: 1rem; height: 1rem; border-width: 0.15em; }
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
                        <input type="hidden" id="apiToken" value="{{ session('owner_api_token') }}">

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

                            <div class="col-12 col-md-6">
                                <label for="lat" class="form-label">Vĩ độ</label>
                                <input id="lat" name="lat" type="number" step="any" class="form-control" value="{{ old('lat') }}">
                                <div class="invalid-feedback" id="error-lat"></div>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="lng" class="form-label">Kinh độ</label>
                                <input id="lng" name="lng" type="number" step="any" class="form-control" value="{{ old('lng') }}">
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

<script>
    const form = document.getElementById('venueForm');
    const alertBox = document.getElementById('formAlert');
    const submitBtn = document.getElementById('submitBtn');
    const submitSpinner = document.getElementById('submitSpinner');
    const submitText = document.getElementById('submitText');
    const previewBox = document.getElementById('previewBox');
    const bannerInput = document.getElementById('banner');
    const token = document.getElementById('apiToken').value;

    const clearErrors = () => {
        document.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach((el) => {
            el.textContent = '';
        });
    };

    const showAlert = (message, type = 'danger') => {
        alertBox.className = `alert alert-${type}`;
        alertBox.textContent = message;
        alertBox.classList.remove('d-none');
    };

    const setSubmitting = (isSubmitting) => {
        submitBtn.disabled = isSubmitting;
        submitSpinner.classList.toggle('d-none', !isSubmitting);
        submitText.textContent = isSubmitting ? 'Đang lưu...' : 'Lưu điểm sân';
    };

    bannerInput.addEventListener('change', (event) => {
        const file = event.target.files && event.target.files[0];
        if (!file) {
            previewBox.innerHTML = '<span class="text-muted">Xem trước banner</span>';
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            previewBox.innerHTML = `<img src="${e.target.result}" alt="Preview banner">`;
        };
        reader.readAsDataURL(file);
    });

    form.addEventListener('reset', () => {
        clearErrors();
        alertBox.classList.add('d-none');
        previewBox.innerHTML = '<span class="text-muted">Xem trước banner</span>';
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrors();
        setSubmitting(true);
        alertBox.classList.add('d-none');

        const formData = new FormData(form);

        try {
            const response = await fetch('/api/owner/venues', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Authorization': token ? `Bearer ${token}` : ''
                },
                body: formData
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
                    showAlert('Vui lòng kiểm tra lại các trường thông tin.', 'warning');
                    return;
                }

                if (response.status === 403) {
                    showAlert('Bạn không có quyền thực hiện chức năng này', 'danger');
                    return;
                }

                showAlert(data.message || 'Không thể tạo điểm sân lúc này.', 'danger');
                return;
            }

            window.location.href = '{{ route('owner.web.venues.index') }}?created=1';
        } catch (error) {
            showAlert('Đã xảy ra lỗi khi kết nối máy chủ.', 'danger');
        } finally {
            setSubmitting(false);
        }
    });
</script>
</body>
</html>
