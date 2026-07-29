<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết hợp đồng - Chủ sân</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .card { background: #fff; border: 1px solid #e2e8f0; border-radius: 1rem; box-shadow: 0 10px 30px rgba(15, 23, 42, .04); }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen">
    <nav class="bg-white shadow-sm border-b border-slate-200 px-6 py-4 flex justify-between items-center">
        <div>
            <a href="{{ route('owner.dashboard') }}" class="text-xl font-bold text-slate-800">SportHub</a>
        </div>
        <div class="flex items-center gap-4 text-sm text-slate-600">
            <a href="{{ route('owner.dashboard') }}" class="hover:text-emerald-600">Dashboard</a>
            <a href="{{ route('owner.venues.index') }}" class="hover:text-emerald-600">Cơ sở</a>
            <a href="{{ route('owner.contracts.index') }}" class="text-emerald-600 font-semibold">Hợp đồng</a>
        </div>
    </nav>

    <main class="max-w-6xl mx-auto p-6">
        <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Chi tiết hợp đồng</h1>
                <p class="text-slate-500">Xem thông tin hợp đồng của bạn.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('owner.contracts.index') }}" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-slate-700 bg-slate-100 rounded-lg hover:bg-slate-200">Quay lại</a>

                @if($contract->status === 'sent')
                    <form action="{{ route('owner.contracts.accept', $contract) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn đồng ý hợp đồng này?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700">Đồng ý hợp đồng</button>
                    </form>

                    <button type="button" class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700" data-bs-toggle="modal" data-bs-target="#rejectContractModal">
                        Từ chối hợp đồng
                    </button>
                @endif
            </div>
        </div>

        @if($contract->status === 'rejected' && $contract->rejection_reason)
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-900">
                <p class="font-semibold">Lý do từ chối:</p>
                <p class="whitespace-pre-line mt-2">{{ $contract->rejection_reason }}</p>
            </div>
        @endif

        <!-- Reject contract modal -->
        <div class="modal fade" id="rejectContractModal" tabindex="-1" aria-labelledby="rejectContractModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="rejectContractModalLabel">Từ chối hợp đồng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('owner.contracts.reject', $contract->id) }}">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="rejection_reason" class="form-label">Lý do từ chối</label>
                                <textarea id="rejection_reason" name="rejection_reason" rows="5" class="form-control @error('rejection_reason') is-invalid @enderror">{{ old('rejection_reason') }}</textarea>
                                @error('rejection_reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Mô tả chi tiết lý do từ chối tối thiểu 10 ký tự.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button type="submit" class="btn btn-danger">Xác nhận từ chối</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 card p-6">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-slate-900 mb-2">Thông tin hợp đồng</h2>
                    <p class="text-slate-500">Chi tiết nội dung và điều khoản hợp đồng.</p>
                </div>

                <div class="grid gap-4">
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Mã hợp đồng</p>
                        <p class="font-semibold text-slate-900">{{ $contract->contract_code }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Tiêu đề</p>
                        <p class="font-semibold text-slate-900">{{ $contract->title }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Nội dung</p>
                        <div class="prose prose-sm text-slate-700">{!! nl2br(e($contract->content)) !!}</div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Hoa hồng</p>
                            <p class="font-semibold text-slate-900">{{ number_format($contract->commission_rate, 2) }}%</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Trạng thái</p>
                            @php
                                $badge = match($contract->status) {
                                    'draft' => 'bg-slate-200 text-slate-700',
                                    'sent' => 'bg-blue-100 text-blue-700',
                                    'accepted' => 'bg-emerald-100 text-emerald-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    'expired' => 'bg-amber-100 text-amber-700',
                                    'terminated' => 'bg-slate-800 text-white',
                                    default => 'bg-slate-200 text-slate-700',
                                };
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ ucfirst($contract->status) }}</span>
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Ngày bắt đầu</p>
                            <p class="font-semibold text-slate-900">{{ $contract->start_date?->format('Y-m-d') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Ngày kết thúc</p>
                            <p class="font-semibold text-slate-900">{{ $contract->end_date?->format('Y-m-d') }}</p>
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Ngày tạo</p>
                            <p class="font-semibold text-slate-900">{{ $contract->created_at->format('Y-m-d H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Ngày cập nhật</p>
                            <p class="font-semibold text-slate-900">{{ $contract->updated_at->format('Y-m-d H:i') }}</p>
                        </div>
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Ngày ký hợp đồng</p>
                            <p class="font-semibold text-slate-900">{{ $contract->signed_at?->format('Y-m-d H:i') ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-slate-500 mb-1">Ghi chú</p>
                            <p class="font-semibold text-slate-900">{{ $contract->note ?? '-' }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Lý do từ chối</p>
                        <p class="font-semibold text-slate-900">{{ $contract->rejection_reason ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="card p-6">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-slate-900">Thông tin Admin</h2>
                    <p class="text-slate-500">Người tạo hợp đồng.</p>
                </div>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Họ và tên</p>
                        <p class="font-semibold text-slate-900">{{ $contract->creator?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Email</p>
                        <p class="font-semibold text-slate-900">{{ $contract->creator?->email ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
