<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu rút tiền | SportHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="min-h-screen text-slate-800 antialiased">
    <nav class="sticky top-0 z-50 flex items-center justify-between border-b border-slate-200 bg-white px-6 py-4 shadow-sm">
        <div class="flex items-center gap-6">
            <a href="{{ route('owner.dashboard') }}" class="text-2xl font-black text-emerald-700">SportHub</a>
            <div class="hidden items-center gap-2 border-l border-slate-200 pl-5 text-sm text-slate-500 md:flex">
                <a href="{{ route('owner.dashboard') }}" class="font-semibold hover:text-emerald-600">Dashboard</a>
                <span>/</span>
                <span class="font-bold text-slate-800">Yêu cầu rút tiền</span>
            </div>
        </div>

        <div class="flex items-center gap-5">
            <a href="{{ route('owner.dashboard') }}" class="text-sm font-semibold text-slate-600 hover:text-emerald-600">Tổng quan</a>
            <a href="{{ route('owner.web.calendar.index') }}" class="text-sm font-semibold text-slate-600 hover:text-emerald-600">Lịch đặt sân</a>
            <a href="{{ route('owner.web.packages.index') }}" class="text-sm font-semibold text-slate-600 hover:text-emerald-600">Quản lý gói</a>
            @include('owner.partials.notification-bell')
        </div>
    </nav>

    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col justify-between gap-4 md:flex-row md:items-end">
            <div>
                <a href="{{ route('owner.dashboard') }}" class="text-sm font-bold text-emerald-700 hover:text-emerald-800">
                    ← Quay lại tổng quan
                </a>
                <h1 class="mt-3 text-3xl font-black text-slate-900">
                    Yêu cầu rút tiền
                </h1>
                <p class="mt-2 text-sm text-slate-500">
                    Theo dõi các yêu cầu rút tiền từ ví chủ sân của bạn.
                </p>
            </div>

            <a href="{{ route('owner.web.withdrawals.create') }}"
               class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-emerald-700">
                Tạo yêu cầu rút tiền
            </a>
        </div>

        @if(session('success'))
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-bold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <section class="mb-6 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">Số dư ví</p>
                <p class="mt-2 text-2xl font-black {{ ($wallet?->balance ?? 0) < 0 ? 'text-red-600' : 'text-emerald-700' }}">
                    {{ number_format($wallet?->balance ?? 0, 0, ',', '.') }}đ
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">Yêu cầu chờ duyệt</p>
                <p class="mt-2 text-2xl font-black text-amber-600">
                    {{ $pendingWithdrawalCount }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">Tổng yêu cầu</p>
                <p class="mt-2 text-2xl font-black text-slate-900">
                    {{ $withdrawals->total() }}
                </p>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Mã yêu cầu</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Số tiền</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Ngân hàng</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Số tài khoản</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Trạng thái</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Ngày gửi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($withdrawals as $withdrawal)
                            @php
                                $statusValue = $withdrawal->status instanceof \BackedEnum ? $withdrawal->status->value : $withdrawal->status;
                                $statusLabels = [
                                    'pending' => ['Chờ duyệt', 'bg-amber-50 text-amber-700 ring-amber-200'],
                                    'approved' => ['Đã duyệt', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
                                    'rejected' => ['Từ chối', 'bg-red-50 text-red-700 ring-red-200'],
                                    'cancelled' => ['Đã hủy', 'bg-slate-100 text-slate-600 ring-slate-200'],
                                ];
                                [$statusText, $statusClass] = $statusLabels[$statusValue] ?? [$statusValue, 'bg-slate-100 text-slate-600 ring-slate-200'];
                            @endphp
                            <tr class="transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-black text-slate-900">
                                    {{ $withdrawal->code }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-black text-emerald-700">
                                    {{ number_format($withdrawal->amount, 0, ',', '.') }}đ
                                </td>
                                <td class="px-5 py-4 text-sm font-bold text-slate-800">
                                    {{ $withdrawal->bank_name }}
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    <div class="font-bold text-slate-800">{{ $withdrawal->bank_account_number ?? $withdrawal->bank_account_no }}</div>
                                    <div class="mt-1 text-xs font-semibold uppercase text-slate-400">{{ $withdrawal->bank_account_holder ?? $withdrawal->bank_account_name }}</div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-black ring-1 {{ $statusClass }}">
                                        {{ $statusText }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-slate-600">
                                    {{ $withdrawal->created_at?->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center">
                                    <p class="text-base font-black text-slate-800">Chưa có yêu cầu rút tiền nào.</p>
                                    <p class="mt-2 text-sm text-slate-500">Khi bạn tạo yêu cầu rút tiền, thông tin sẽ hiển thị ở đây.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-6">
            {{ $withdrawals->links() }}
        </div>
    </main>

    @include('owner.partials.notification-script')
</body>
</html>
