@extends('admin.layouts.app')

@push('styles')
<style>
    .page-head { display: flex; justify-content: space-between; gap: 20px; align-items: flex-start; margin-bottom: 24px; }
    .back-link { color: #047857; display: inline-flex; font-size: 13px; font-weight: 800; margin-bottom: 10px; text-decoration: none; }
    .page-title { color: var(--text-dark); font-size: 24px; font-weight: 900; margin: 0; }
    .page-subtitle { color: var(--text-muted); font-size: 14px; margin-top: 6px; }
    .detail-grid { display: grid; grid-template-columns: 1.1fr .9fr; gap: 22px; }
    .info-card { background: #fff; border: 1px solid var(--border-color); border-radius: 14px; box-shadow: 0 2px 10px rgba(0,0,0,.02); padding: 22px; }
    .info-card + .info-card { margin-top: 18px; }
    .card-title { color: var(--text-dark); font-size: 17px; font-weight: 900; margin-bottom: 18px; }
    .info-row { display: grid; grid-template-columns: 170px 1fr; gap: 18px; padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
    .info-row:last-child { border-bottom: 0; }
    .info-label { color: var(--text-muted); font-size: 13px; font-weight: 800; }
    .info-value { color: var(--text-dark); font-size: 14px; font-weight: 700; }
    .money { color: #047857; font-size: 22px; font-weight: 900; }
    .badge-status { display: inline-flex; align-items: center; border-radius: 999px; padding: 7px 12px; font-size: 12px; font-weight: 900; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-approved { background: #dcfce7; color: #166534; }
    .status-rejected { background: #fee2e2; color: #b91c1c; }
    .status-cancelled { background: #f1f5f9; color: #475569; }
    .note-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; color: var(--text-dark); font-size: 14px; line-height: 1.6; min-height: 54px; padding: 14px; }
    .action-card { background: #fffbeb; border-color: #fde68a; }
    .warning-box { background: #fff7ed; border: 1px solid #fed7aa; border-radius: 12px; color: #9a3412; font-size: 13px; font-weight: 700; line-height: 1.55; margin-bottom: 16px; padding: 13px 14px; }
    .form-control-soft { width: 100%; border: 1px solid #cbd5e1; border-radius: 10px; color: var(--text-dark); font-size: 14px; outline: none; padding: 12px 14px; resize: vertical; }
    .form-control-soft:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(46,204,113,.12); }
    .btn-row { display: flex; gap: 12px; margin-top: 14px; }
    .btn-approve, .btn-reject, .btn-outline-soft { align-items: center; border-radius: 10px; border: 1px solid transparent; cursor: pointer; display: inline-flex; font-size: 13px; font-weight: 900; justify-content: center; min-height: 42px; padding: 0 16px; text-decoration: none; }
    .btn-approve { background: #10b981; color: #fff; }
    .btn-reject { background: #ef4444; color: #fff; }
    .btn-outline-soft { background: #fff; border-color: var(--border-color); color: var(--text-dark); }
    .alert-success, .alert-error { border-radius: 12px; font-size: 13px; font-weight: 700; margin-bottom: 18px; padding: 13px 16px; }
    .alert-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; }
    .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; }
    @media (max-width: 980px) {
        .page-head, .detail-grid, .info-row, .btn-row { display: block; }
        .detail-grid > * + *, .btn-row > * + * { margin-top: 12px; }
        .info-label { margin-bottom: 5px; }
    }
</style>
@endpush

@section('content')
@php
    $statusValue = $withdrawal->status instanceof \BackedEnum ? $withdrawal->status->value : $withdrawal->status;
    $statusLabels = [
        'pending' => ['Chờ duyệt', 'status-pending'],
        'approved' => ['Đã duyệt', 'status-approved'],
        'rejected' => ['Từ chối', 'status-rejected'],
        'cancelled' => ['Đã hủy', 'status-cancelled'],
    ];
    [$statusText, $statusClass] = $statusLabels[$statusValue] ?? [$statusValue, 'status-cancelled'];
    $walletBalance = (float) ($withdrawal->wallet?->balance ?? 0);
    $withdrawAmount = (float) $withdrawal->amount;
@endphp

@if(session('success'))
    <div class="alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert-error">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert-error">{{ $errors->first() }}</div>
@endif

<div class="page-head">
    <div>
        <a class="back-link" href="{{ route('admin.withdrawals.index') }}">← Quay lại danh sách</a>
        <h2 class="page-title">Chi tiết yêu cầu rút tiền</h2>
        <div class="page-subtitle">{{ $withdrawal->code }} · Tạo lúc {{ $withdrawal->created_at?->format('H:i d/m/Y') }}</div>
    </div>

    <span class="badge-status {{ $statusClass }}">{{ $statusText }}</span>
</div>

<div class="detail-grid">
    <div>
        <section class="info-card">
            <div class="card-title">Thông tin yêu cầu</div>

            <div class="info-row">
                <div class="info-label">Mã yêu cầu</div>
                <div class="info-value">{{ $withdrawal->code }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Số tiền muốn rút</div>
                <div class="info-value money">{{ number_format($withdrawal->amount, 0, ',', '.') }}đ</div>
            </div>
            <div class="info-row">
                <div class="info-label">Trạng thái</div>
                <div class="info-value"><span class="badge-status {{ $statusClass }}">{{ $statusText }}</span></div>
            </div>
            <div class="info-row">
                <div class="info-label">Ngày gửi</div>
                <div class="info-value">{{ $withdrawal->created_at?->format('H:i d/m/Y') }}</div>
            </div>

            @if($withdrawal->approved_at)
                <div class="info-row">
                    <div class="info-label">Ngày duyệt</div>
                    <div class="info-value">{{ $withdrawal->approved_at->format('H:i d/m/Y') }}</div>
                </div>
            @endif

            @if($withdrawal->rejected_at)
                <div class="info-row">
                    <div class="info-label">Ngày từ chối</div>
                    <div class="info-value">{{ $withdrawal->rejected_at->format('H:i d/m/Y') }}</div>
                </div>
            @endif
        </section>

        <section class="info-card">
            <div class="card-title">Thông tin chủ sân</div>

            <div class="info-row">
                <div class="info-label">Tên chủ sân</div>
                <div class="info-value">{{ $withdrawal->owner?->name ?? 'Không rõ' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email</div>
                <div class="info-value">{{ $withdrawal->owner?->email ?? 'Chưa cập nhật' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Số điện thoại</div>
                <div class="info-value">{{ $withdrawal->owner?->phone ?? 'Chưa cập nhật' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Số dư ví hiện tại</div>
                <div class="info-value money">{{ number_format($walletBalance, 0, ',', '.') }}đ</div>
            </div>
        </section>
    </div>

    <div>
        <section class="info-card">
            <div class="card-title">Thông tin ngân hàng</div>

            <div class="info-row">
                <div class="info-label">Ngân hàng</div>
                <div class="info-value">{{ $withdrawal->bank_name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Số tài khoản</div>
                <div class="info-value">{{ $withdrawal->bank_account_number ?? $withdrawal->bank_account_no }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Chủ tài khoản</div>
                <div class="info-value">{{ $withdrawal->bank_account_holder ?? $withdrawal->bank_account_name }}</div>
            </div>

            <!-- MÃ VIETQR TỰ ĐỘNG (CHỈ HIỂN THỊ KHI ĐƠN ĐANG CHỜ DUYỆT) -->
            @if($statusValue === 'pending')
                @php
                    $bankMap = [
                        'Vietcombank' => 'VCB', 'Techcombank' => 'TCB', 'MBBank' => 'MB',
                        'VietinBank' => 'CTG', 'BIDV' => 'BIDV', 'ACB' => 'ACB',
                        'VPBank' => 'VPB', 'Agribank' => 'VBA', 'TPBank' => 'TPB',
                        'Sacombank' => 'STB'
                    ];
                    $bankCode = $bankMap[$withdrawal->bank_name] ?? $withdrawal->bank_name;
                    $transferNote = urlencode("Thanh toan rut tien " . $withdrawal->code);
                    $accountName = urlencode($withdrawal->bank_account_holder ?? $withdrawal->bank_account_name);
                    $amount = (int) $withdrawal->amount;
                    
                    $qrUrl = "https://img.vietqr.io/image/{$bankCode}-".($withdrawal->bank_account_number ?? $withdrawal->bank_account_no)."-compact2.png?amount={$amount}&addInfo={$transferNote}&accountName={$accountName}";
                @endphp
                
                <div style="margin-top: 24px; background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px dashed #cbd5e1; text-align: center;">
                    <div style="color: #059669; font-size: 13px; font-weight: 800; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.05em;">
                        <i class="fa-solid fa-qrcode"></i> Quét mã để chuyển khoản nhanh
                    </div>
                    <img src="{{ $qrUrl }}" alt="VietQR Code" style="max-width: 200px; border-radius: 8px; margin: 0 auto; display: block; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
                </div>
            @endif
        </section>

        @if($statusValue === 'pending')
            <section class="info-card action-card">
                <div class="card-title">Xử lý yêu cầu</div>

                <div class="warning-box">
                    Khi duyệt, hệ thống sẽ trừ số tiền này khỏi ví chủ sân và ghi nhận tiền ra khỏi ví nền tảng SportHub.
                </div>

                @if($walletBalance < $withdrawAmount)
                    <div class="alert-error">
                        Số dư ví hiện tại không đủ để duyệt yêu cầu này.
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.withdrawals.approve', $withdrawal) }}">
                    @csrf
                    <button class="btn-approve" type="submit">Duyệt, trừ ví chủ sân và ví nền tảng</button>
                </form>

                <form method="POST" action="{{ route('admin.withdrawals.reject', $withdrawal) }}" style="margin-top: 18px;">
                    @csrf
                    <label class="info-label" for="admin_note">Lý do từ chối hoặc ghi chú admin</label>
                    <textarea class="form-control-soft"
                              id="admin_note"
                              name="admin_note"
                              rows="4"
                              placeholder="Ví dụ: Thông tin tài khoản chưa chính xác, vui lòng kiểm tra lại.">{{ old('admin_note') }}</textarea>

                    <div class="btn-row">
                        <button class="btn-reject" type="submit">Từ chối yêu cầu</button>
                        <a class="btn-outline-soft" href="{{ route('admin.withdrawals.index') }}">Hủy thao tác</a>
                    </div>
                </form>
            </section>
        @else
            <section class="info-card">
                <div class="card-title">Thông tin xử lý</div>
                <div class="info-row">
                    <div class="info-label">Người duyệt/xử lý</div>
                    <div class="info-value">{{ $withdrawal->approver?->name ?? 'Chưa ghi nhận' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Trạng thái hiện tại</div>
                    <div class="info-value"><span class="badge-status {{ $statusClass }}">{{ $statusText }}</span></div>
                </div>
            </section>
        @endif
    </div>
</div>
@endsection
