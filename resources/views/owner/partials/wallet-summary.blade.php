@php
    $summary = $ownerWalletSummary ?? null;
@endphp

@if($summary)
    @php
        $formatMoney = fn ($amount) => number_format(abs((float) $amount), 0, ',', '.') . 'đ';
        $balance = (float) ($summary['balance'] ?? 0);
        $debtAmount = (float) ($summary['debt_amount'] ?? 0);
        $debtLimit = (float) ($summary['debt_limit'] ?? 0);
        $usagePercent = (float) ($summary['usage_percent'] ?? 0);
        $progressPercent = min(100, max(0, $usagePercent));
        $status = $summary['status'] ?? 'good';
        $statusLabels = [
            'good' => 'An toàn',
            'in_debt' => 'Đang nợ',
            'warning' => 'Gần hạn mức',
            'over_limit' => 'Vượt hạn mức',
        ];
        $statusClasses = [
            'good' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
            'in_debt' => 'bg-amber-50 text-amber-700 ring-amber-100',
            'warning' => 'bg-orange-50 text-orange-700 ring-orange-100',
            'over_limit' => 'bg-red-50 text-red-700 ring-red-100',
        ];
        $barClass = match ($status) {
            'over_limit' => 'bg-red-500',
            'warning' => 'bg-orange-500',
            'in_debt' => 'bg-amber-500',
            default => 'bg-emerald-500',
        };
    @endphp

    <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">Ví chủ sân</p>
                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-black ring-1 {{ $statusClasses[$status] ?? $statusClasses['good'] }}">
                        {{ $statusLabels[$status] ?? $status }}
                    </span>
                </div>

                <div class="mt-3 flex flex-wrap items-end gap-x-8 gap-y-3">
                    <div>
                        <p class="text-sm font-bold text-slate-500">Số dư hiện tại</p>
                        <p class="mt-1 text-3xl font-black {{ $balance < 0 ? 'text-red-600' : 'text-emerald-700' }}">
                            {{ $balance < 0 ? '-' : '' }}{{ $formatMoney($balance) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-bold text-slate-500">Công nợ</p>
                        <p class="mt-1 text-xl font-black {{ $debtAmount > 0 ? 'text-red-600' : 'text-slate-900' }}">
                            {{ $formatMoney($debtAmount) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-sm font-bold text-slate-500">Hạn mức</p>
                        <p class="mt-1 text-xl font-black text-slate-900">{{ $formatMoney($debtLimit) }}</p>
                    </div>

                    <div>
                        <p class="text-sm font-bold text-slate-500">Đã dùng hạn mức</p>
                        <p class="mt-1 text-xl font-black text-slate-900">{{ number_format($usagePercent, 0, ',', '.') }}%</p>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-64">
                <div class="h-2.5 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full {{ $barClass }}" style="width: {{ $progressPercent }}%"></div>
                </div>
                <div class="mt-4 flex flex-wrap gap-2">
                    @if(Route::has('owner.web.wallet.index'))
                        <a href="{{ route('owner.web.wallet.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-black text-slate-700 transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700">
                            Xem ví
                        </a>
                    @endif

                    @if(Route::has('owner.web.wallet.topup.create'))
                        <a href="{{ route('owner.web.wallet.topup.create') }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-black text-white transition hover:bg-emerald-700">
                            Nạp tiền
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
