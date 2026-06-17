@extends('admin.layouts.app')

@section('title', 'Quản lý Báo cáo Vi phạm')

@push('styles')
<style>
    .page-header {
        margin-bottom: 24px;
    }
    .page-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .page-desc {
        color: var(--text-muted);
        font-size: 14px;
    }
    .report-card {
        background: var(--card-bg);
        border-radius: var(--radius-md);
        border: 1px solid var(--border-color);
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        overflow: hidden;
    }
    .table-responsive {
        width: 100%;
        overflow-x: auto;
    }
    .report-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
    }
    .report-table th {
        background-color: #f8f9fa;
        color: var(--text-muted);
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 16px 20px;
        text-align: left;
        border-bottom: 1px solid var(--border-color);
    }
    .report-table td {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border-color);
        font-size: 14px;
        color: var(--text-dark);
        vertical-align: middle;
    }
    .report-table tbody tr:hover {
        background-color: #fafbfc;
    }
    .custom-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .badge-pending {
        background-color: #fff3cd;
        color: #856404;
    }
    .badge-resolved {
        background-color: var(--primary-light);
        color: var(--primary);
    }
    .btn-action {
        background-color: var(--primary);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
    }
    .btn-action:hover {
        background-color: #27ae60;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(46, 204, 113, 0.2);
    }
    .empty-state {
        padding: 60px 20px;
        text-align: center;
    }
    .empty-state i {
        font-size: 48px;
        color: #bdc3c7;
        margin-bottom: 16px;
    }
    .empty-state h5 {
        font-size: 18px;
        color: var(--text-dark);
        margin-bottom: 8px;
    }
    .empty-state p {
        color: var(--text-muted);
        font-size: 14px;
    }
    .pagination-container {
        padding: 16px 20px;
        border-top: 1px solid var(--border-color);
        background: #fff;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">
        <i class="fa-solid fa-triangle-exclamation" style="color: #e74c3c;"></i> Quản lý Báo cáo Vi phạm
    </h1>
    <p class="page-desc">Kiểm tra và xử lý các sân có dấu hiệu vi phạm được người dùng phản ánh.</p>
</div>

@if(session('success'))
<div style="background-color: var(--primary-light); border: 1px solid var(--primary); color: var(--primary); padding: 16px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-weight: 500;">
    <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
</div>
@endif

<div class="report-card">
    @if($reports->count() > 0)
        <div class="table-responsive">
            <table class="report-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 20%;">Người gửi</th>
                        <th style="width: 20%;">Cơ sở / Sân</th>
                        <th style="width: 25%;">Lý do phản ánh</th>
                        <th style="width: 15%;">Ngày gửi</th>
                        <th style="width: 15%; text-align: right;">Trạng thái & Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td><strong style="color: var(--text-muted);">#{{ $report->id }}</strong></td>
                        <td>
                            <div style="font-weight: 600; margin-bottom: 4px;">{{ $report->user->name }}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">{{ $report->user->email }}</div>
                        </td>
                        <td>
                            @if($report->court)
                                <div style="color: var(--primary); font-weight: 600; margin-bottom: 4px;">{{ $report->court->venue->name ?? 'N/A' }}</div>
                                <div style="font-size: 12px; color: var(--text-muted);"><i class="fa-solid fa-layer-group" style="font-size: 10px;"></i> Sân: {{ $report->court->name }}</div>
                            @else
                                <div style="color: #e74c3c; font-style: italic; font-size: 13px;">Sân đã bị xóa</div>
                            @endif
                        </td>
                        <td>
                            <div style="font-size: 13px; line-height: 1.5; color: var(--text-dark);">
                                {{ $report->reason }}
                            </div>
                        </td>
                        <td style="color: var(--text-muted); font-size: 13px;">
                            {{ $report->created_at->format('d/m/Y') }}<br>
                            <small>{{ $report->created_at->format('H:i') }}</small>
                        </td>
                        <td style="text-align: right;">
                            @if($report->status === 'pending')
                                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                                    <span class="custom-badge badge-pending">
                                        <i class="fa-regular fa-clock"></i> Chờ xử lý
                                    </span>
                                    <form action="{{ route('admin.reports.update-status', $report->id) }}" method="POST" style="margin: 0;">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-action" onclick="return confirm('Xác nhận đã xử lý xong báo cáo này?');">
                                            <i class="fa-solid fa-check"></i> Đã xử lý
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="custom-badge badge-resolved">
                                    <i class="fa-solid fa-check-double"></i> Đã giải quyết
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($reports->hasPages())
            <div class="pagination-container">
                {{ $reports->links() }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <i class="fa-solid fa-shield-halved"></i>
            <h5>Hệ thống đang an toàn!</h5>
            <p>Hiện tại không có báo cáo vi phạm nào cần xử lý.</p>
        </div>
    @endif
</div>
@endsection