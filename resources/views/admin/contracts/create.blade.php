@extends('admin.layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent px-0 mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.contracts.index') }}">Hợp đồng</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tạo hợp đồng</li>
                </ol>
            </nav>
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <h1 class="h3 mb-1">Tạo hợp đồng mới</h1>
                    <p class="text-muted mb-0">Điền thông tin hợp đồng để gửi cho Chủ sân.</p>
                </div>
                <a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i> Quay lại
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">Thông tin hợp đồng</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.contracts.store') }}" method="POST">
                        @csrf

                        <div class="row g-4">
                            <div class="col-12 col-md-6">
                                <label for="owner_id" class="form-label">Chủ sân</label>
                                <select name="owner_id" id="owner_id" class="form-select" required>
    <option value="">Chọn chủ sân</option>
    @foreach($owners as $owner)
        <!-- Dùng request('owner_id') để tự động chọn nếu URL có chứa id -->
        <option value="{{ $owner->id }}" 
            {{ old('owner_id', request('owner_id')) == $owner->id ? 'selected' : '' }}>
            {{ $owner->name }}
        </option>
    @endforeach
</select>
                                @error('owner_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
    <label for="venue_id" class="form-label">Cơ sở liên kết <span class="text-danger">*</span></label>
   <select name="venue_id" id="venue_id" class="form-select" required>
    <option value="">-- Chọn cơ sở liên kết --</option>
    @foreach($venues as $venue)
        <!-- Thêm data-owner-id để JS nhận diện được sân này của ai -->
        <option value="{{ $venue->id }}" 
            data-owner-id="{{ $venue->owner_id }}"
            {{ old('venue_id', request('venue_id')) == $venue->id ? 'selected' : '' }}>
            {{ $venue->name }}
        </option>
    @endforeach
</select>
    
    @error('venue_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

                            <div class="col-12 col-md-6">
                                <label for="title" class="form-label">Tiêu đề hợp đồng</label>
                                <input type="text" id="title" name="title" value="{{ old('title', 'HỢP ĐỒNG HỢP TÁC KINH DOANH') }}" class="form-control @error('title') is-invalid @enderror" placeholder="Ví dụ: Hợp tác kinh doanh mùa giải 2026">
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="alert alert-info mb-0">
                                    <div class="fw-semibold mb-1">Khung hợp đồng được tạo tự động</div>
                                    <div class="small">Admin chỉ nhập các thông tin cần thiết như chủ sân, cơ sở, hoa hồng và thời hạn. Toàn bộ văn bản pháp lý sẽ được hệ thống render từ mẫu chuẩn.</div>
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="commission_rate" class="form-label">Tỷ lệ hoa hồng (%)</label>
                                <input type="number" step="0.01" id="commission_rate" name="commission_rate" value="{{ old('commission_rate') }}" class="form-control @error('commission_rate') is-invalid @enderror" placeholder="10.00">
                                @error('commission_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="start_date" class="form-label">Ngày bắt đầu</label>
                                <input type="date" id="start_date" name="start_date" value="{{ old('start_date') }}" class="form-control @error('start_date') is-invalid @enderror">
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="end_date" class="form-label">Ngày kết thúc</label>
                                <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" class="form-control @error('end_date') is-invalid @enderror">
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="note" class="form-label">Ghi chú</label>
                                <textarea id="note" name="note" rows="3" class="form-control @error('note') is-invalid @enderror" placeholder="Thêm ghi chú nội bộ...">{{ old('note') }}</textarea>
                                @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mt-4 d-flex flex-column flex-sm-row gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save2 me-2"></i> Lưu hợp đồng
                            </button>
                            <a href="{{ route('admin.contracts.index') }}" class="btn btn-outline-secondary">Hủy</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ownerSelect = document.getElementById('owner_id');
        const venueSelect = document.getElementById('venue_id');
        
        // Tạo một mảng lưu trữ gốc để tránh bị lỗi mất data khi ẩn/hiện trên một số trình duyệt
        const venueOptions = Array.from(venueSelect.querySelectorAll('option:not([value=""])'));

        function filterVenues() {
            const selectedOwnerId = ownerSelect.value;

            // 1. NẾU CHƯA CHỌN CHỦ SÂN: Khóa ô Cơ sở và ẩn tất cả tùy chọn
            if (!selectedOwnerId) {
                venueSelect.value = ""; // Reset về "Không gắn..."
                venueSelect.disabled = true; // Bôi xám, khóa ô select
                
                venueOptions.forEach(option => {
                    option.hidden = true; 
                    option.style.display = 'none';
                });
                return; // Dừng hàm tại đây
            }

            // 2. NẾU ĐÃ CHỌN CHỦ SÂN: Mở khóa và bắt đầu lọc
            venueSelect.disabled = false;
            
            venueOptions.forEach(option => {
                if (option.dataset.ownerId === selectedOwnerId) {
                    option.hidden = false;
                    option.style.display = ''; // Hiện cơ sở đúng của chủ sân
                } else {
                    option.hidden = true;
                    option.style.display = 'none'; // Ẩn cơ sở của người khác
                }
            });

            // 3. Xử lý trường hợp đang chọn Chủ A + Sân A, đổi sang Chủ B thì sân bị sai
            const currentVenueOption = venueSelect.options[venueSelect.selectedIndex];
            if (currentVenueOption && currentVenueOption.value !== "" && currentVenueOption.dataset.ownerId !== selectedOwnerId) {
                venueSelect.value = ""; // Tự động trả về "Không gắn..."
            }
        }

        // Kích hoạt sự kiện lắng nghe
        if (ownerSelect && venueSelect) {
            ownerSelect.addEventListener('change', filterVenues);
            
            // Chạy ngay lần đầu tiên để ẩn danh sách lúc vừa vào trang (hoặc lúc load form Edit)
            filterVenues();
        }
    });
</script>
@endpush
