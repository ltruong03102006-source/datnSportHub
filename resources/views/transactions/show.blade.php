@extends('layouts.app')

@section('content')
<div class="container py-5">
    <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary mb-4">← Quay lại</a>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold mb-1">Chi tiết giao dịch</h3>
                    <p class="text-muted mb-0">Thông tin đầy đủ về giao dịch của bạn.</p>
                </div>
                <span class="badge {{ $transaction->status_badge_class }} fs-6">{{ $transaction->status_label }}</span>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <p class="mb-2"><strong>Mã giao dịch:</strong> {{ $transaction->transaction_code }}</p>
                    <p class="mb-2"><strong>Mã đặt sân:</strong> #{{ $transaction->booking_id }}</p>
                    <p class="mb-2"><strong>Tên khách hàng:</strong> {{ $transaction->user->name ?? '—' }}</p>
                    <p class="mb-2"><strong>Email:</strong> {{ $transaction->user->email ?? '—' }}</p>
                    <p class="mb-2"><strong>Tên sân:</strong> {{ $transaction->booking->court->venue->name ?? '—' }}</p>
                    <p class="mb-2"><strong>Địa chỉ sân:</strong> {{ $transaction->booking->court->venue->address ?? '—' }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-2"><strong>Khung giờ đặt:</strong> {{ $transaction->booking->start_time }} - {{ $transaction->booking->end_time }}</p>
                    <p class="mb-2"><strong>Ngày đặt:</strong> {{ optional($transaction->booking->slot_date)->format('d/m/Y') ?? '—' }}</p>
                    <p class="mb-2"><strong>Tổng tiền:</strong> {{ number_format($transaction->amount, 0, ',', '.') }}₫</p>
                    <p class="mb-2"><strong>Phương thức thanh toán:</strong> {{ $transaction->payment_method }}</p>
                    <p class="mb-2"><strong>Cổng thanh toán:</strong> {{ $transaction->payment_gateway ?? '—' }}</p>
                    <p class="mb-2"><strong>Thời gian giao dịch:</strong> {{ optional($transaction->transaction_time)->format('d/m/Y H:i') ?? '—' }}</p>
                    <p class="mb-2"><strong>Ghi chú:</strong> {{ $transaction->note ?? 'Không có' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
