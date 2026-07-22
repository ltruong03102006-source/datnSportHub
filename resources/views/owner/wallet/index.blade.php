<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ví của tôi | SportHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="min-h-screen text-slate-800 antialiased">
    @php
        $money = fn ($amount) => number_format(abs((float) $amount), 0, ',', '.') . 'đ';
        $signedMoney = fn ($amount) => ((float) $amount < 0 ? '-' : '') . number_format(abs((float) $amount), 0, ',', '.') . 'đ';
        $transactionTypeLabels = [
            'booking_income' => 'Nhận tiền booking online',
            'booking_online_credit' => 'Nhận tiền booking online',
            'commission_fee' => 'Trừ hoa hồng COD',
            'commission_cod_debit' => 'Trừ hoa hồng COD',
            'topup' => 'Nạp tiền vào ví',
            'topup_credit' => 'Nạp tiền vào ví',
            'deposit' => 'Nạp tiền vào ví',
            'withdraw' => 'Rút tiền',
            'withdrawal_debit' => 'Rút tiền',
            'withdrawal_rejected_refund' => 'Hoàn lại yêu cầu rút',
            'manual_adjustment' => 'Điều chỉnh thủ công',
            'adjustment' => 'Điều chỉnh thủ công',
            'refund' => 'Hoàn tiền',
            'refund_debit' => 'Hoàn tiền',
        ];
        $debitTypes = ['commission_fee', 'commission_cod_debit', 'withdraw', 'withdrawal_debit', 'refund_debit', 'payment'];
        $typeValue = fn ($transaction) => $transaction->type instanceof \BackedEnum ? $transaction->type->value : (string) $transaction->type;
        $displayAmount = fn ($transaction) => in_array($typeValue($transaction), $debitTypes, true)
            ? -abs((float) $transaction->amount)
            : (float) $transaction->amount;
        $debtStatusMessages = [
            'good' => 'Ví của bạn đang an toàn.',
            'in_debt' => 'Bạn đang có công nợ nhưng chưa vượt hạn mức.',
            'warning' => 'Công nợ đã đạt gần hạn mức. Vui lòng nạp tiền để tránh bị khóa cơ sở.',
            'over_limit' => 'Công nợ đã vượt hạn mức. Cơ sở có thể bị tạm khóa cho đến khi bạn nạp tiền trả nợ.',
        ];
        $debtStatus = $debtSummary['status'] ?? 'good';
        $usagePercent = min(100, (float) ($debtSummary['usage_percent'] ?? 0));
        $isWarning = in_array($debtStatus, ['warning', 'over_limit'], true);
    @endphp

    <nav class="sticky top-0 z-50 flex items-center justify-between border-b border-slate-200 bg-white px-6 py-4 shadow-sm">
        <div class="flex items-center gap-6">
            <a href="{{ route('owner.dashboard') }}" class="text-2xl font-black text-emerald-700">SportHub</a>
            <div class="hidden items-center gap-2 border-l border-slate-200 pl-5 text-sm text-slate-500 md:flex">
                <a href="{{ route('owner.dashboard') }}" class="font-semibold hover:text-emerald-600">Dashboard</a>
                <span>/</span>
                <span class="font-bold text-slate-800">Ví của tôi</span>
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
        <div class="mb-6 flex flex-col justify-between gap-4 lg:flex-row lg:items-end">
            <div>
                <a href="{{ route('owner.dashboard') }}" class="text-sm font-bold text-emerald-700 hover:text-emerald-800">
                    ← Quay lại tổng quan
                </a>
                <h1 class="mt-3 text-3xl font-black text-slate-900">Ví của tôi</h1>
                <p class="mt-2 text-sm text-slate-500">
                    Theo dõi số dư ví, công nợ và lịch sử giao dịch của bạn.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('owner.web.wallet.topup.create') }}"
                   class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-emerald-700">
                    Nạp tiền
                </a>
                <a href="{{ route('owner.web.withdrawals.create') }}"
                   class="inline-flex items-center justify-center rounded-xl border border-emerald-600 bg-white px-5 py-3 text-sm font-extrabold text-emerald-700 transition hover:bg-emerald-50">
                    Rút tiền
                </a>
                <a href="{{ route('owner.web.withdrawals.index') }}"
                   class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50">
                    Lịch sử rút tiền
                </a>
            </div>
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

        <section class="grid gap-5 lg:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-1">
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">Số dư ví hiện tại</p>
                <p class="mt-3 text-4xl font-black {{ $wallet->balance < 0 ? 'text-red-600' : 'text-emerald-700' }}">
                    {{ $signedMoney($wallet->balance) }}
                </p>
                <div class="mt-4 inline-flex rounded-full px-3 py-1 text-xs font-black {{ $wallet->balance < 0 ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }}">
                    {{ $wallet->balance < 0 ? 'Đang nợ nền tảng' : 'Đang hoạt động' }}
                </div>
            </div>

            <div class="rounded-2xl border {{ $isWarning ? 'border-amber-200 bg-amber-50' : 'border-slate-200 bg-white' }} p-6 shadow-sm lg:col-span-1">
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">Công nợ</p>
                <div class="mt-3 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-bold text-slate-500">Hiện tại</p>
                        <p class="mt-1 text-2xl font-black text-red-600">{{ $money($debtSummary['debt_amount'] ?? 0) }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-slate-500">Hạn mức</p>
                        <p class="mt-1 text-2xl font-black text-slate-900">{{ $money($debtSummary['debt_limit'] ?? 0) }}</p>
                    </div>
                </div>
                <div class="mt-5">
                    <div class="mb-2 flex justify-between text-xs font-black text-slate-500">
                        <span>Tỷ lệ sử dụng</span>
                        <span>{{ number_format($debtSummary['usage_percent'] ?? 0, 0, ',', '.') }}%</span>
                    </div>
                    <div class="h-3 overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full rounded-full {{ ($debtSummary['is_over_limit'] ?? false) ? 'bg-red-500' : (($debtSummary['is_near_limit'] ?? false) ? 'bg-amber-500' : 'bg-emerald-500') }}"
                             style="width: {{ $usagePercent }}%"></div>
                    </div>
                </div>
                <p class="mt-4 text-sm font-bold {{ $debtStatus === 'over_limit' ? 'text-red-700' : ($debtStatus === 'warning' ? 'text-amber-700' : 'text-slate-600') }}">
                    {{ $debtStatusMessages[$debtStatus] ?? $debtStatusMessages['good'] }}
                </p>
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-1">
                <p class="text-xs font-black uppercase tracking-wider text-slate-400">Đang chờ rút</p>
                <p class="mt-3 text-4xl font-black text-amber-600">{{ $money($pendingWithdrawAmount) }}</p>
                <p class="mt-4 text-sm font-semibold text-slate-500">
                    Tổng số tiền trong các yêu cầu rút tiền đang chờ admin duyệt.
                </p>
            </div>
        </section>

        <section class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase text-slate-400">Đã nạp</p>
                <p class="mt-2 text-xl font-black text-emerald-700">{{ $money($totalTopup) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase text-slate-400">Đã rút</p>
                <p class="mt-2 text-xl font-black text-red-600">{{ $money($totalWithdrawal) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase text-slate-400">Booking online</p>
                <p class="mt-2 text-xl font-black text-emerald-700">{{ $money($totalBookingOnlineCredit) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase text-slate-400">Hoa hồng COD</p>
                <p class="mt-2 text-xl font-black text-orange-600">{{ $money($totalCommissionCodDebit) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-black uppercase text-slate-400">Giao dịch</p>
                <p class="mt-2 text-xl font-black text-slate-900">{{ number_format($totalTransactions, 0, ',', '.') }}</p>
            </div>
        </section>

        <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('owner.web.wallet.index') }}" class="grid gap-3 lg:grid-cols-[1fr_180px_180px_auto_auto]">
                <select name="type"
                        class="rounded-xl border border-slate-300 px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                    <option value="">Tất cả loại giao dịch</option>
                    @foreach($transactionTypes as $transactionType)
                        <option value="{{ $transactionType }}" @selected($type === $transactionType)>
                            {{ $transactionTypeLabels[$transactionType] ?? $transactionType }}
                        </option>
                    @endforeach
                </select>

                <input type="date"
                       name="date_from"
                       value="{{ $dateFrom }}"
                       class="rounded-xl border border-slate-300 px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">

                <input type="date"
                       name="date_to"
                       value="{{ $dateTo }}"
                       class="rounded-xl border border-slate-300 px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">

                <button type="submit"
                        class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-extrabold text-white hover:bg-emerald-700">
                    Lọc
                </button>

                <a href="{{ route('owner.web.wallet.index') }}"
                   class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-extrabold text-slate-700 hover:bg-slate-50">
                    Xóa lọc
                </a>
            </form>
        </section>

        <section class="mt-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4">
                <h2 class="text-xl font-black text-slate-900">Lịch sử giao dịch ví</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Thời gian</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Loại giao dịch</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Mô tả</th>
                            <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Số tiền</th>
                            <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Trước</th>
                            <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wider text-slate-500">Sau</th>
                            <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wider text-slate-500">Tham chiếu</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($transactions as $transaction)
                            @php
                                $currentType = $typeValue($transaction);
                                $shownAmount = $displayAmount($transaction);
                                $isCredit = $shownAmount >= 0;
                            @endphp
                            <tr class="transition hover:bg-slate-50">
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-800">
                                    {{ $transaction->created_at?->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-black {{ $isCredit ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700' }}">
                                        {{ $transactionTypeLabels[$currentType] ?? $currentType }}
                                    </span>
                                </td>
                                <td class="min-w-[260px] px-5 py-4 text-sm font-semibold text-slate-700">
                                    {{ $transaction->description }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-black {{ $isCredit ? 'text-emerald-700' : 'text-red-600' }}">
                                    {{ $isCredit ? '+' : '-' }}{{ $money($shownAmount) }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-bold text-slate-600">
                                    {{ $signedMoney($transaction->balance_before) }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-bold text-slate-900">
                                    {{ $signedMoney($transaction->balance_after) }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold text-slate-500">
                                    {{ $transaction->reference ?: 'Không có' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-12 text-center">
                                    <p class="text-base font-black text-slate-800">Chưa có giao dịch ví nào.</p>
                                    <p class="mt-2 text-sm text-slate-500">Khi ví phát sinh giao dịch, lịch sử sẽ hiển thị ở đây.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="mt-6">
            {{ $transactions->links() }}
        </div>
    </main>

    @include('owner.partials.notification-script')
</body>
</html>
