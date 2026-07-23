@extends('admin.layouts.app')

@section('content')
<div style="padding: 20px;">
    <!-- Tiêu đề -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2 style="margin: 0; color: #2c3e50; font-weight: 700;">Quản lý Yêu cầu Chuyển nhượng</h2>
    </div>

    <!-- Thông báo -->
    @if(session('success'))
        <div style="background-color: #d4edda; color: #155724; padding: 12px 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #c3e6cb;">
            {{ session('success') }}
        </div>
    @endif

    <!-- Bảng Dữ liệu -->
    <div class="card-custom" style="padding: 0; overflow: hidden;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background-color: #f8f9fa; border-bottom: 2px solid #ecf0f1;">
                    <th style="padding: 16px; color: #7f8c8d; font-weight: 600; font-size: 13px;">Mã YC</th>
                    <th style="padding: 16px; color: #7f8c8d; font-weight: 600; font-size: 13px;">Cơ sở (Venue)</th>
                    <th style="padding: 16px; color: #7f8c8d; font-weight: 600; font-size: 13px;">Bên Bán (Chủ cũ)</th>
                    <th style="padding: 16px; color: #7f8c8d; font-weight: 600; font-size: 13px;">Bên Mua (Chủ mới)</th>
                    <th style="padding: 16px; color: #7f8c8d; font-weight: 600; font-size: 13px;">Thời gian</th>
                    <th style="padding: 16px; color: #7f8c8d; font-weight: 600; font-size: 13px; text-align: center;">Trạng thái</th>
                    <th style="padding: 16px; color: #7f8c8d; font-weight: 600; font-size: 13px; text-align: center;">Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transfers as $transfer)
                    <tr style="border-bottom: 1px solid #ecf0f1; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f8f9fa'" onmouseout="this.style.backgroundColor='transparent'">
                        <td style="padding: 16px; font-weight: 600; color: #2c3e50;">#{{ $transfer->id }}</td>
                        <td style="padding: 16px; color: #2ecc71; font-weight: 600;">{{ $transfer->venue->name ?? 'N/A' }}</td>
                        <td style="padding: 16px;">
                            <div style="font-weight: 600; color: #2c3e50; margin-bottom: 4px;">{{ $transfer->fromOwner->name ?? $transfer->fromOwner->full_name ?? 'N/A' }}</div>
                            <div style="font-size: 12px; color: #7f8c8d;">{{ $transfer->fromOwner->email ?? '' }}</div>
                        </td>
                        <td style="padding: 16px;">
                            <div style="font-weight: 600; color: #2c3e50; margin-bottom: 4px;">{{ $transfer->toOwner->name ?? $transfer->toOwner->full_name ?? 'N/A' }}</div>
                            <div style="font-size: 12px; color: #7f8c8d;">{{ $transfer->toOwner->email ?? '' }}</div>
                        </td>
                        <td style="padding: 16px; color: #7f8c8d; font-size: 14px;">{{ $transfer->created_at->format('d/m/Y H:i') }}</td>
                        <td style="padding: 16px; text-align: center;">
                            @if($transfer->status === 'pending')
                                <span style="background-color: #fff3cd; color: #856404; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Chờ duyệt</span>
                            @elseif($transfer->status === 'approved')
                                <span style="background-color: #d4edda; color: #155724; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Đã duyệt</span>
                            @elseif($transfer->status === 'rejected')
                                <span style="background-color: #f8d7da; color: #721c24; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">Từ chối</span>
                            @endif
                        </td>
                        <td style="padding: 16px; text-align: center;">
                            <a href="{{ route('admin.venue-transfers.show', $transfer->id) }}" style="display: inline-block; background-color: #eafaf1; color: #2ecc71; text-decoration: none; padding: 6px 16px; border-radius: 6px; font-weight: 600; font-size: 13px; border: 1px solid #2ecc71;">Xem chi tiết</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding: 30px; text-align: center; color: #7f8c8d;">Chưa có yêu cầu chuyển nhượng nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        @if($transfers->hasPages())
            <div style="padding: 16px; border-top: 1px solid #ecf0f1;">
                {{ $transfers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection