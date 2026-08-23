@extends('admin.layouts.app')

@section('content')

<style>
    .legal-container{
        max-width:1200px;
        margin:auto;
    }

    .page-title{
        font-size:28px;
        font-weight:700;
        margin-bottom:25px;
        color:#2c3e50;
    }

    .section-card{
        background:#fff;
        border-radius:14px;
        padding:25px;
        margin-bottom:20px;
        box-shadow:0 2px 10px rgba(0,0,0,.05);
        border:1px solid #eee;
    }

    .section-title{
        font-size:18px;
        font-weight:700;
        color:#34495e;
        margin-bottom:20px;
        padding-bottom:10px;
        border-bottom:2px solid #f1f1f1;
    }

    .info-table{
        width:100%;
    }

    .info-table tr{
        border-bottom:1px solid #f3f4f6;
    }

    .info-table td{
        padding:12px 0;
    }

    .info-label{
        width:220px;
        font-weight:600;
        color:#64748b;
    }

    .info-value{
        font-weight:500;
        color:#111827;
    }

    .document-grid{
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
        gap:20px;
    }

    .document-card{
        border:1px solid #e5e7eb;
        border-radius:12px;
        overflow:hidden;
        background:#fff;
        text-align:center;
        transition:.3s;
    }

    .document-card:hover{
        transform:translateY(-4px);
        box-shadow:0 6px 18px rgba(0,0,0,.08);
    }

    .document-card img{
        width:100%;
        height:180px;
        object-fit:cover;
        border-bottom:1px solid #eee;
    }

    .document-body{
        padding:15px;
    }

    .document-title{
        font-weight:700;
        margin-bottom:10px;
    }

    .btn-view{
        display:inline-block;
        padding:8px 14px;
        background:#2563eb;
        color:#fff;
        border-radius:8px;
        text-decoration:none;
    }

    .btn-view:hover{
        color:white;
        background:#1d4ed8;
    }

    .btn-approve{
        background:#16a34a;
        color:white;
        border:none;
        padding:10px 20px;
        border-radius:8px;
        font-weight:600;
        cursor: pointer;
    }
    
    .btn-approve:hover{ background:#15803d; }

    .btn-reject{
        background:#dc2626;
        color:white;
        border:none;
        padding:10px 20px;
        border-radius:8px;
        font-weight:600;
        cursor: pointer;
    }
    
    .btn-reject:hover{ background:#b91c1c; }

    .btn-back{
        margin-bottom:20px;
        display:inline-flex;
        align-items:center;
        gap:8px;
        border:1px solid #cbd5e1;
        border-radius:9px;
        background:#fff;
        color:#334155;
        padding:9px 14px;
        font-size:13px;
        font-weight:700;
        text-decoration:none;
        transition:.2s;
    }
    .btn-back:hover{background:#f8fafc;color:#047857;border-color:#86efac;transform:translateX(-2px);}

    /* Style cho khung cảnh báo có bản nháp */
    .alert-update-request {
        background: #fffbeb;
        border-left: 5px solid #f59e0b;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        box-shadow: 0 2px 15px rgba(0,0,0,.04);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>

<div class="legal-container">

<a href="{{ route('admin.venues.index') }}" class="btn-back">
    <i class="fa-solid fa-arrow-left"></i> Quay lại
</a>

<h2 class="page-title">
    Hồ sơ pháp lý cơ sở
</h2>

{{-- 1. THANH CẢNH BÁO & BẢNG ĐỐI CHIẾU: XỬ LÝ YÊU CẦU THAY ĐỔI THÔNG TIN --}}
@if($venue->pendingUpdateRequest)
    <div style="background: #fff; border: 1px solid #f59e0b; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); overflow: hidden; font-family: Inter, sans-serif;">
        
        <!-- Header -->
        <div style="background: #fffbeb; border-bottom: 1px solid #fcd34d; padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h5 style="margin: 0 0 5px 0; color: #b45309; font-size: 18px; font-weight: 700;">
                    <i class="fa-solid fa-code-compare" style="margin-right: 8px;"></i>Yêu cầu thay đổi hồ sơ pháp lý!
                </h5>
                <p style="margin: 0; color: #92400e; font-size: 13px;">
                    Gửi lúc: <strong>{{ $venue->pendingUpdateRequest->created_at->format('d/m/Y H:i') }}</strong>. Vui lòng đối chiếu cẩn thận trước khi duyệt.
                </p>
            </div>
            <div style="display: flex; gap: 10px;">
                <form action="{{ route('admin.venues.update-requests.reject', $venue->pendingUpdateRequest->id) }}" method="POST" onsubmit="return promptRejectReason(this, event);" style="margin: 0;">
                    @csrf
                    <input type="hidden" name="admin_note" class="reject-reason-input">
                    <button type="submit" style="background: #dc2626; color: white; border: none; padding: 9px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(220, 38, 38, 0.2);">
                        <i class="fa-solid fa-xmark"></i> Từ chối
                    </button>
                </form>
                
                <form action="{{ route('admin.venues.update-requests.approve', $venue->pendingUpdateRequest->id) }}" method="POST" onsubmit="return confirm('Ghi đè toàn bộ hồ sơ pháp lý mới cho sân này?');" style="margin: 0;">
                    @csrf
                    <button type="submit" style="background: #16a34a; color: white; border: none; padding: 9px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 4px rgba(22, 163, 74, 0.2);">
                        <i class="fa-solid fa-check-double"></i> Phê duyệt
                    </button>
                </form>
            </div>
        </div>

        <!-- Bảng đối chiếu -->
        @php
            $newData = is_array($venue->pendingUpdateRequest->requested_data) 
                        ? $venue->pendingUpdateRequest->requested_data 
                        : json_decode($venue->pendingUpdateRequest->requested_data, true);
            
            $textFields = [
                'owner_name' => 'Tên Chủ sở hữu',
                'citizen_id' => 'Số CCCD',
                'business_license_number' => 'Số GPKD / Mã số thuế',
                'bank_name' => 'Ngân hàng',
                'bank_account_number' => 'Số tài khoản',
                'bank_account_holder' => 'Chủ tài khoản',
            ];

            $fileFields = [
                'citizen_front_image' => 'CCCD Mặt trước',
                'citizen_back_image' => 'CCCD Mặt sau',
                'business_license_file' => 'Giấy phép KD',
                'rental_contract_file' => 'Hợp đồng thuê',
                'land_certificate_file' => 'Sổ đỏ/Sổ hồng',
            ];
            $hasChanges = false;
        @endphp
        
        <div style="padding: 0;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 14px;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                        <th style="padding: 12px 20px; color: #64748b; text-transform: uppercase; font-size: 12px; width: 25%;">Trường dữ liệu</th>
                        <th style="padding: 12px 20px; color: #64748b; text-transform: uppercase; font-size: 12px; width: 35%;">Dữ liệu Hiện tại (Cũ)</th>
                        <th style="padding: 12px 20px; color: #64748b; text-transform: uppercase; font-size: 12px; width: 40%;">Dữ liệu Yêu cầu (Mới)</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- ĐỐI CHIẾU CHỮ --}}
                    @foreach($textFields as $key => $label)
                        @php $oldVal = $venue->legalDocument->$key ?? ''; @endphp
                        @if(array_key_exists($key, $newData) && trim($newData[$key]) != trim($oldVal))
                            @php $hasChanges = true; @endphp
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 16px 20px; font-weight: 600; color: #334155;">{{ $label }}</td>
                                <td style="padding: 16px 20px;">
                                    <span style="background: #f1f5f9; color: #94a3b8; padding: 4px 8px; border-radius: 4px; text-decoration: line-through;">{{ $oldVal ?: 'Trống' }}</span>
                                </td>
                                <td style="padding: 16px 20px;">
                                    <span style="background: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 4px; font-weight: 600;">{{ $newData[$key] ?: 'Yêu cầu xóa' }}</span>
                                </td>
                            </tr>
                        @endif
                    @endforeach

                    {{-- ĐỐI CHIẾU FILE --}}
                    @foreach($fileFields as $key => $label)
                        @if(!empty($newData[$key]))
                            @php 
                                $hasChanges = true; 
                                $oldPath = $venue->legalDocument->$key ?? null;
                            @endphp
                            <tr style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 16px 20px; font-weight: 600; color: #334155;">
                                    {{ $label }}
                                    <span style="background: #ef4444; color: white; padding: 2px 8px; border-radius: 12px; font-size: 11px; margin-left: 8px;">Tệp mới</span>
                                </td>
                                <td style="padding: 16px 20px;">
                                    @if($oldPath)
                                        <a href="{{ asset('storage/'.$oldPath) }}" target="_blank" style="text-decoration: none; color: #475569; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; display: inline-block;">Xem tệp Cũ</a>
                                    @else
                                        <span style="color: #94a3b8; font-style: italic; font-size: 13px;">Chưa có tệp</span>
                                    @endif
                                </td>
                                <td style="padding: 16px 20px;">
                                    <a href="{{ asset('storage/'.$newData[$key]) }}" target="_blank" style="text-decoration: none; color: white; background: #0ea5e9; padding: 6px 12px; border-radius: 6px; font-size: 13px; font-weight: 600; display: inline-block;">Xem tệp MỚI</a>
                                </td>
                            </tr>
                        @endif
                    @endforeach

                    @if(!$hasChanges)
                        <tr>
                            <td colspan="3" style="padding: 30px; text-align: center; color: #94a3b8;">Không phát hiện sự thay đổi dữ liệu nào.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- XỬ LÝ DUYỆT / TỪ CHỐI CƠ SỞ --}}
@if($venue->status === 'pending')
    <div style="background: #eff6ff; border: 1px solid #93c5fd; border-radius: 12px; padding: 20px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div>
            <h5 style="margin: 0 0 4px 0; color: #1e40af; font-size: 18px; font-weight: 700;">
                <i class="fa-solid fa-clock-rotate-left" style="margin-right: 8px;"></i>Cơ sở đang chờ bạn duyệt!
            </h5>
            <p style="margin: 0; color: #1e3a8a; font-size: 14px;">
                Vui lòng kiểm tra kỹ thông tin cơ sở & hồ sơ đính kèm bên dưới trước khi phê duyệt hoặc từ chối.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <form action="{{ route('admin.venues.approve', $venue->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn phê duyệt cơ sở sân này?');" style="margin:0;">
                @csrf
                <button type="submit" class="btn-approve" style="display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-check"></i> Duyệt cơ sở
                </button>
            </form>
            <form action="{{ route('admin.venues.reject', $venue->id) }}" method="POST" onsubmit="return promptRejectReason(this, event);" style="margin:0;">
                @csrf
                <input type="hidden" name="reject_reason" class="reject-reason-input">
                <button type="submit" class="btn-reject" style="display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-xmark"></i> Từ chối
                </button>
            </form>
        </div>
    </div>
@elseif($venue->status === 'approved' || $venue->status === 'active')
    <div style="background: #f0fdf4; border: 1px solid #86efac; border-radius: 12px; padding: 16px 20px; margin-bottom: 25px; color: #166534; font-weight: 600; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-check" style="font-size: 20px;"></i>
        <span>Cơ sở này đã được phê duyệt (Trạng thái: <strong>{{ strtoupper($venue->status) }}</strong>).</span>
    </div>
@elseif($venue->status === 'rejected')
    <div style="background: #fef2f2; border: 1px solid #fca5a5; border-radius: 12px; padding: 16px 20px; margin-bottom: 25px; color: #991b1b; font-weight: 600; display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid fa-circle-xmark" style="font-size: 20px;"></i>
        <span>Cơ sở này đã bị từ chối. {{ $venue->legalDocument?->reject_reason ? 'Lý do: ' . $venue->legalDocument->reject_reason : '' }}</span>
    </div>
@endif

{{-- 2. THÔNG TIN CƠ SỞ (Đã bổ sung SĐT, Email) --}}
<div class="section-card">
    <div class="section-title">Thông tin cơ sở</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Tên cơ sở</td>
            <td class="info-value">{{ $venue->name }}</td>
        </tr>
        <tr>
            <td class="info-label">Số điện thoại</td>
            <td class="info-value">
                @if($venue->phone)
                    <a href="tel:{{ $venue->phone }}" class="text-decoration-none fw-bold text-primary">{{ $venue->phone }}</a>
                @else
                    <span class="text-muted">Chưa cập nhật</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="info-label">Email liên hệ</td>
            <td class="info-value">
                @if($venue->email)
                    <a href="mailto:{{ $venue->email }}" class="text-decoration-none fw-bold text-primary">{{ $venue->email }}</a>
                @else
                    <span class="text-muted">Chưa cập nhật</span>
                @endif
            </td>
        </tr>
        <tr>
            <td class="info-label">Địa chỉ</td>
            <td class="info-value">{{ $venue->address }}</td>
        </tr>
        <tr>
            <td class="info-label">Chủ sân</td>
            <td class="info-value">{{ $venue->owner->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Ngày tạo</td>
            <td class="info-value">{{ $venue->created_at?->format('d/m/Y H:i') }}</td>
        </tr>
    </table>
</div>

{{-- 3. THÔNG TIN PHÁP LÝ --}}
<div class="section-card">
    <div class="section-title">Thông tin pháp lý</div>
    <table class="info-table">
        <tr>
            <td class="info-label">Chủ sở hữu</td>
            <td class="info-value">{{ $venue->legalDocument?->owner_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Số CCCD</td>
            <td class="info-value fw-bold text-primary">{{ $venue->legalDocument?->citizen_id ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Giấy phép KD / Mã số thuế</td>
            <td class="info-value">{{ $venue->legalDocument?->business_license_number ?? '-' }}</td>
        </tr>
    </table>
</div>

{{-- 4. HỒ SƠ ĐÍNH KÈM --}}
<div class="section-card">
    <div class="section-title">Hồ sơ đính kèm</div>

    @if(!$venue->legalDocument)
        <div style="padding: 15px; color: #b45309; background: #fff7ed; border-radius: 8px;">
            Chưa có hồ sơ pháp lý được lưu cho cơ sở này.
        </div>
    @else
    <div class="document-grid">
        @if($venue->legalDocument?->citizen_front_image)
        <div class="document-card">
            <img src="{{ asset('storage/'.$venue->legalDocument?->citizen_front_image) }}">
            <div class="document-body">
                <div class="document-title">CCCD MẶT TRƯỚC</div>
                <a target="_blank" href="{{ asset('storage/'.$venue->legalDocument?->citizen_front_image) }}" class="btn-view">Xem ảnh lớn</a>
            </div>
        </div>
        @endif

        @if($venue->legalDocument?->citizen_back_image)
        <div class="document-card">
            <img src="{{ asset('storage/'.$venue->legalDocument?->citizen_back_image) }}">
            <div class="document-body">
                <div class="document-title">CCCD MẶT SAU</div>
                <a target="_blank" href="{{ asset('storage/'.$venue->legalDocument?->citizen_back_image) }}" class="btn-view">Xem ảnh lớn</a>
            </div>
        </div>
        @endif

        @if($venue->legalDocument?->business_license_file)
        <div class="document-card">
            <div class="document-body" style="padding:50px 20px">
                <i class="fa-solid fa-file-contract" style="font-size:70px;color:#10b981"></i>
                <div class="document-title mt-3">GIẤY PHÉP KINH DOANH</div>
                <a target="_blank" href="{{ asset('storage/'.$venue->legalDocument?->business_license_file) }}" class="btn-view">Xem tài liệu</a>
            </div>
        </div>
        @endif

        @if($venue->legalDocument?->rental_contract_file)
        <div class="document-card">
            <div class="document-body" style="padding:50px 20px">
                <i class="fa-solid fa-file-signature" style="font-size:70px;color:#3b82f6"></i>
                <div class="document-title mt-3">HỢP ĐỒNG THUÊ</div>
                <a target="_blank" href="{{ asset('storage/'.$venue->legalDocument?->rental_contract_file) }}" class="btn-view">Xem tài liệu</a>
            </div>
        </div>
        @endif
        
        @if($venue->legalDocument?->land_certificate_file)
        <div class="document-card">
            <div class="document-body" style="padding:50px 20px">
                <i class="fa-solid fa-map-location-dot" style="font-size:70px;color:#f59e0b"></i>
                <div class="document-title mt-3">SỔ ĐỎ / SỔ HỒNG</div>
                <a target="_blank" href="{{ asset('storage/'.$venue->legalDocument?->land_certificate_file) }}" class="btn-view">Xem tài liệu</a>
            </div>
        </div>
        @endif
    </div>
    @endif
</div>

</div>

<script>
    let currentDocRejectForm = null;

    function promptRejectReason(form, event) {
        if (event) event.preventDefault();
        currentDocRejectForm = form;
        
        const textarea = document.getElementById('rejectDocReasonTextarea');
        const errorDiv = document.getElementById('rejectDocReasonError');
        if (textarea) textarea.value = '';
        if (errorDiv) errorDiv.style.display = 'none';
        
        const modalEl = document.getElementById('rejectDocModal');
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        } else {
            modalEl.classList.add('show');
            modalEl.style.display = 'block';
        }
        
        return false;
    }

    function submitRejectDocForm() {
        const textarea = document.getElementById('rejectDocReasonTextarea');
        const errorDiv = document.getElementById('rejectDocReasonError');
        const reason = textarea ? textarea.value.trim() : '';
        
        if (reason.length < 5) {
            if (errorDiv) errorDiv.style.display = 'block';
            return;
        }
        
        if (errorDiv) errorDiv.style.display = 'none';
        if (currentDocRejectForm) {
            currentDocRejectForm.querySelector('.reject-reason-input').value = reason;
            const modalEl = document.getElementById('rejectDocModal');
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }
            currentDocRejectForm.submit();
        }
    }
</script>

<!-- Modal Nhập Lý Do Từ Chối Hồ Sơ / Cơ Sở -->
<div class="modal fade" id="rejectDocModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header bg-danger text-white border-0 py-3">
                <h5 class="modal-title fw-bold">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i>Từ chối yêu cầu / Hồ sơ
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">Vui lòng nhập lý do từ chối chi tiết bên dưới để thông báo cho Chủ sân:</p>
                <div class="mb-3">
                    <label for="rejectDocReasonTextarea" class="form-label fw-bold text-dark">Lý do từ chối <span class="text-danger">*</span></label>
                    <textarea id="rejectDocReasonTextarea" 
                              class="form-control" 
                              rows="5" 
                              style="border-radius: 10px; resize: vertical; padding: 12px; font-size: 14px;" 
                              placeholder="Vui lòng nhập lý do rõ ràng (VD: CCCD bị mờ, Sai số tài khoản ngân hàng, Giấy phép kinh doanh không khớp thông tin...)"></textarea>
                    <div id="rejectDocReasonError" class="text-danger small mt-1" style="display: none;">Vui lòng nhập lý do từ chối ít nhất 5 ký tự.</div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3 px-4">
                <button type="button" class="btn btn-secondary px-4 fw-semibold" data-bs-dismiss="modal" style="border-radius: 8px;">Hủy bỏ</button>
                <button type="button" onclick="submitRejectDocForm()" class="btn btn-danger px-4 fw-bold" style="border-radius: 8px;">Xác nhận từ chối</button>
            </div>
        </div>
    </div>
</div>
@endsection