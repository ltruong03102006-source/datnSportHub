@extends('admin.layouts.app')

@push('styles')
<style>
    .page-title { margin: 6px 0 0; color: var(--text-dark); font-size: 30px; font-weight: 900; }
    .panel { border: 1px solid var(--border-color); border-radius: 14px; background: #fff; box-shadow: 0 8px 24px rgba(15, 23, 42, .04); margin-top: 24px;}
    .data-table { width: 100%; border-collapse: collapse; }
    .data-table th { padding: 11px 14px; background: #f8fafc; color: #64748b; border-bottom: 1px solid var(--border-color); font-size: 11px; font-weight: 950; text-transform: uppercase; text-align: left;}
    .data-table td { padding: 12px 14px; border-bottom: 1px solid var(--border-color); color: #0f172a; font-size: 13px; }
    .tone-green { color: #047857; }
    .tone-red { color: #dc2626; }
    .btn-soft { display: inline-flex; align-items: center; justify-content: center; min-height: 42px; padding: 0 16px; border-radius: 10px; font-size: 13px; font-weight: 900; text-decoration: none; border: 1px solid var(--border-color); background: #fff; color: var(--text-dark); }
    .tx-badge { padding: 5px 9px; background: #f1f5f9; color: #475569; border-radius: 999px; font-size: 11px; font-weight: 900; }
</style>
@endpush

@section('content')
<div style="display: flex; justify-content: space-between; align-items: flex-start;">
    <div>
        <div style="color: #059669; font-size: 12px; font-weight: 900; letter-spacing: .08em; text-transform: uppercase;">Admin Finance</div>
        <h2 class="page-title">Lịch sử rút doanh thu</h2>
    </div>
    <a href="{{ route('admin.finance.index') }}" class="btn-soft">⬅ Quay lại Tổng quan</a>
</div>

<div class="panel">
    <table class="data-table">
        <thead>
            <tr>
                <th>Thời gian</th>
                <th>Mã Giao Dịch</th>
                <th>Người thực hiện</th>
                <th>Số tiền</th>
                <th>Phân loại</th>
                <th>Mô tả chi tiết</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $tx)
                <tr>
                    <td>
                        <strong>{{ $tx->created_at->format('d/m/Y') }}</strong>
                        <div style="color: #64748b; font-size: 11px; font-weight: 700;">{{ $tx->created_at->format('H:i') }}</div>
                    </td>
                    <td><strong>{{ $tx->reference }}</strong></td>
                    <td><strong>{{ $tx->performer->name ?? 'Hệ thống' }}</strong></td>
                    <td class="{{ $tx->amount < 0 ? 'tone-red' : 'tone-green' }}">
                        <strong>{{ $tx->amount < 0 ? '-' : '+' }}{{ number_format(abs($tx->amount), 0, ',', '.') }}đ</strong>
                    </td>
                    <td>
                        @if($tx->type === 'admin_revenue_refund')
                            <span class="tx-badge" style="background:#fef2f2; color:#dc2626;">Hoàn tiền (Lỗi GD)</span>
                        @else
                            <span class="tx-badge" style="background:#ecfdf5; color:#047857;">Rút doanh thu</span>
                        @endif
                    </td>
                    <td style="color: #64748b;">{{ $tx->description }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding: 36px; text-align: center;"><strong>Chưa có giao dịch rút doanh thu nào.</strong></td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
    <!-- Phân trang -->
    <div style="padding: 16px;">
        {{ $transactions->links() }}
    </div>
</div>
@endsection