@extends('admin.layouts.app')

@section('content')
<div class="card-custom">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Lịch sử giao dịch thanh toán</h3>
            <p class="text-muted mb-0">Quản trị viên xem toàn bộ giao dịch của hệ thống.</p>
        </div>
    </div>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label">Tìm kiếm</label>
            <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Mã giao dịch, tên khách, email, mã đơn">
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
        <div class="col-md-2">
            <label class="form-label">Từ ngày</label>
            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Đến ngày</label>
            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
        </div>
        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-success w-100">Lọc</button>
        </div>
    </form>

    @if ($transactions->isEmpty())
        <div class="alert alert-info">Chưa có giao dịch nào trong hệ thống.</div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>STT</th>
                        <th>Mã giao dịch</th>
                        <th>Khách hàng</th>
                        <th>Email</th>
                        <th>Mã đơn</th>
                        <th>Số tiền</th>
                        <th>Phương thức</th>
                        <th>Trạng thái</th>
                        <th>Thời gian</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $index => $transaction)
                        <tr>
                            <td>{{ $transactions->firstItem() + $index }}</td>
                            <td>{{ $transaction->transaction_code }}</td>
                            <td>{{ $transaction->user->name ?? '—' }}</td>
                            <td>{{ $transaction->user->email ?? '—' }}</td>
                            <td>#{{ $transaction->booking_id }}</td>
                            <td>{{ number_format($transaction->amount, 0, ',', '.') }}₫</td>
                            <td>{{ $transaction->payment_method }}</td>
                            <td><span class="badge {{ $transaction->status_badge_class }}">{{ $transaction->status_label }}</span></td>
                            <td>{{ optional($transaction->transaction_time)->format('d/m/Y H:i') ?? '—' }}</td>
                            <td><a href="{{ route('admin.transactions.show', $transaction) }}" class="btn btn-sm btn-outline-primary">Xem</a></td>
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
