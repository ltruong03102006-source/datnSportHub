@extends('admin.layouts.app')

@section('title', 'Yêu cầu chuyển nhượng cơ sở')

@section('content')
<div class="container-fluid p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Quản lý Chuyển nhượng Cơ sở</h1>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-sm font-medium text-slate-500 uppercase tracking-wider">
                        <th class="p-4">ID</th>
                        <th class="p-4">Cơ sở (Venue)</th>
                        <th class="p-4">Người bán (Chủ cũ)</th>
                        <th class="p-4">Người mua (Chủ mới)</th>
                        <th class="p-4">Thời gian tạo</th>
                        <th class="p-4">Trạng thái</th>
                        <th class="p-4 text-center">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @forelse($transfers as $transfer)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="p-4 text-sm text-slate-600">#{{ $transfer->id }}</td>
                            <td class="p-4">
                                <span class="text-sm font-semibold text-slate-800">{{ $transfer->venue->name ?? 'N/A' }}</span>
                            </td>
                            <td class="p-4">
                                <div class="text-sm text-slate-800">{{ $transfer->fromOwner->name ?? $transfer->fromOwner->full_name ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-500">{{ $transfer->fromOwner->email ?? '' }}</div>
                            </td>
                            <td class="p-4">
                                <div class="text-sm text-slate-800">{{ $transfer->toOwner->name ?? $transfer->toOwner->full_name ?? 'N/A' }}</div>
                                <div class="text-xs text-slate-500">{{ $transfer->toOwner->email ?? '' }}</div>
                            </td>
                            <td class="p-4 text-sm text-slate-600">
                                {{ $transfer->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="p-4">
                                @if($transfer->status === 'pending')
                                    <span class="px-2.5 py-1 text-xs font-medium bg-amber-100 text-amber-700 rounded-full">Chờ duyệt</span>
                                @elseif($transfer->status === 'approved')
                                    <span class="px-2.5 py-1 text-xs font-medium bg-emerald-100 text-emerald-700 rounded-full">Đã duyệt</span>
                                @elseif($transfer->status === 'rejected')
                                    <span class="px-2.5 py-1 text-xs font-medium bg-red-100 text-red-700 rounded-full">Từ chối</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <a href="{{ route('admin.venue-transfers.show', $transfer->id) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-sm font-medium transition-colors">
                                    Chi tiết
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-slate-500 text-sm">
                                Chưa có yêu cầu chuyển nhượng nào.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Phân trang -->
        @if($transfers->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $transfers->links() }}
            </div>
        @endif
    </div>
</div>
@endsection