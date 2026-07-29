<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hợp đồng của tôi - Chủ sân</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .table-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 1rem; box-shadow: 0 10px 30px rgba(15, 23, 42, .04); }
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

    <main class="max-w-7xl mx-auto p-6">
        <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-3">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Hợp đồng của tôi</h1>
                <p class="text-slate-500">Xem danh sách hợp đồng dành cho Chủ sân.</p>
            </div>
        </div>

        <div class="table-card p-6">
            @if($contracts->isEmpty())
                <div class="py-16 text-center text-slate-500">
                    Bạn chưa có hợp đồng nào.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead>
                            <tr class="text-slate-700 bg-slate-100">
                                <th class="p-3 font-semibold">STT</th>
                                <th class="p-3 font-semibold">Mã hợp đồng</th>
                                <th class="p-3 font-semibold">Tiêu đề</th>
                                <th class="p-3 font-semibold">Hoa hồng</th>
                                <th class="p-3 font-semibold">Ngày bắt đầu</th>
                                <th class="p-3 font-semibold">Ngày kết thúc</th>
                                <th class="p-3 font-semibold">Trạng thái</th>
                                <th class="p-3 font-semibold">Ngày tạo</th>
                                <th class="p-3 font-semibold">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contracts as $index => $contract)
                                <tr class="border-t border-slate-200">
                                    <td class="p-3 align-top">{{ $contracts->firstItem() + $index }}</td>
                                    <td class="p-3 align-top font-medium">{{ $contract->contract_code }}</td>
                                    <td class="p-3 align-top">{{ $contract->title }}</td>
                                    <td class="p-3 align-top">{{ number_format($contract->commission_rate, 2) }}%</td>
                                    <td class="p-3 align-top">{{ $contract->start_date?->format('Y-m-d') }}</td>
                                    <td class="p-3 align-top">{{ $contract->end_date?->format('Y-m-d') }}</td>
                                    <td class="p-3 align-top">
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
                                    </td>
                                    <td class="p-3 align-top">{{ $contract->created_at->format('Y-m-d') }}</td>
                                    <td class="p-3 align-top">
                                        <a href="{{ route('owner.contracts.show', $contract) }}" class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg">Xem chi tiết</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $contracts->links() }}
                </div>
            @endif
        </div>
    </main>
</body>
</html>
