@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Lịch sử giao dịch thanh toán</h2>
            <p class="text-muted mb-0">Theo dõi các giao dịch của bạn sau khi đặt sân.</p>
        </div>
        <a href="{{ route('account.bookings.index') }}" class="btn btn-outline-secondary">Quay lại lịch sử đặt sân</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label">Mã giao dịch</label>
            <input type="text" name="search_code" class="form-control" value="{{ request('search_code') }}" placeholder="Nhập mã giao dịch">
        </div>
        <div class="col-md-3">
            <label class="form-label">Mã đơn</label>
            <input type="text" name="search_booking" class="form-control" value="{{ request('search_booking') }}" placeholder="Nhập mã đơn">
        </div>
        <div class="col-md-2">
            <label class="form-label">Trạng thái</label>
            <select name="status" class="form-select">
                <option value="">Tất cả</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Đang chờ</option>
                <option value="success" {{ request('status') === 'success' ? 'selected' : '' }}>Thành công</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Thất bại</option>
                <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Hoàn tiền</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Phương thức</label>
            <select name="payment_method" class="form-select">
                <option value="">Tất cả</option>
                @foreach ($paymentMethods as $method)
                    <option value="{{ $method }}" {{ request('payment_method') === $method ? 'selected' : '' }}>{{ $method }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-success w-100">Lọc</button>
        </div>
    </form>

    @if ($transactions->isEmpty())
        <div class="alert alert-info">Chưa có giao dịch nào phù hợp.</div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>STT</th>
                        <th>Mã giao dịch</th>
                        <th>Mã đơn</th>
                        <th>Tên sân</th>
                        <th>Ngày đặt sân</th>
                        <th>Số tiền</th>
                        <th>Phương thức</th>
                        <th>Trạng thái</th>
                        <th>Thời gian thanh toán</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $index => $transaction)
                        <tr>
                            <td>{{ $transactions->firstItem() + $index }}</td>
                            <td>{{ $transaction->transaction_code }}</td>
                            <td>#{{ $transaction->booking_id }}</td>
                            <td>{{ $transaction->booking->court->venue->name ?? '—' }}</td>
                            <td>{{ optional($transaction->booking->slot_date)->format('d/m/Y') ?? '—' }}</td>
                            <td>{{ number_format($transaction->amount, 0, ',', '.') }}₫</td>
                            <td>{{ $transaction->payment_method }}</td>
                            <td><span class="badge {{ $transaction->status_badge_class }}">{{ $transaction->status_label }}</span></td>
                            <td>{{ optional($transaction->transaction_time)->format('d/m/Y H:i') ?? '—' }}</td>
                            <td>
                                <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-sm btn-outline-primary">Xem chi tiết</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">
            {{ $transactions->links() }}
        </div>
    @endif
</div>
@endsection
