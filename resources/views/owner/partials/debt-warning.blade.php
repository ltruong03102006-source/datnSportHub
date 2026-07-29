@php
    $summary = $ownerWalletSummary ?? null;
    $debtAmount = (float) ($summary['debt_amount'] ?? 0);
@endphp

@if($summary && $debtAmount > 0)
    @php
        $formatMoney = fn ($amount) => number_format(abs((float) $amount), 0, ',', '.') . 'đ';
        $status = $summary['status'] ?? 'in_debt';
        $usagePercent = (float) ($summary['usage_percent'] ?? 0);
        $styles = [
            'in_debt' => [
                'wrap' => 'border-amber-200 bg-amber-50 text-amber-900',
                'icon' => 'bg-amber-100 text-amber-700',
                'button' => 'bg-amber-600 hover:bg-amber-700 text-white',
                'title' => 'Bạn đang có công nợ',
                'message' => 'Bạn đang có công nợ ' . $formatMoney($debtAmount) . '. Vui lòng theo dõi để tránh vượt hạn mức.',
            ],
            'warning' => [
                'wrap' => 'border-orange-200 bg-orange-50 text-orange-950',
                'icon' => 'bg-orange-100 text-orange-700',
                'button' => 'bg-orange-600 hover:bg-orange-700 text-white',
                'title' => 'Công nợ gần chạm hạn mức',
                'message' => 'Công nợ của bạn đã đạt ' . number_format($usagePercent, 0, ',', '.') . '% hạn mức. Vui lòng nạp tiền để tránh bị tạm khóa cơ sở.',
            ],
            'over_limit' => [
                'wrap' => 'border-red-200 bg-red-50 text-red-950',
                'icon' => 'bg-red-100 text-red-700',
                'button' => 'bg-red-600 hover:bg-red-700 text-white',
                'title' => 'Công nợ đã vượt hạn mức',
                'message' => 'Công nợ của bạn đã vượt hạn mức. Cơ sở của bạn có thể bị tạm khóa cho đến khi bạn nạp tiền trả nợ.',
            ],
        ];
        $current = $styles[$status] ?? $styles['in_debt'];
    @endphp

    <div class="mb-6 rounded-2xl border p-4 shadow-sm {{ $current['wrap'] }}">
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
            <div class="flex gap-3">
                <div class="mt-0.5 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $current['icon'] }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                    </svg>
                </div>
                <div>
                    <p class="font-black">{{ $current['title'] }}</p>
                    <p class="mt-1 text-sm font-semibold leading-6 opacity-90">{{ $current['message'] }}</p>
                </div>
            </div>

            <div class="flex shrink-0 flex-wrap gap-2">
                @if(Route::has('owner.web.wallet.topup.create'))
                    <a href="{{ route('owner.web.wallet.topup.create') }}" class="inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-black transition {{ $current['button'] }}">
                        Nạp tiền ngay
                    </a>
                @endif

                @if(Route::has('owner.web.wallet.index'))
                    <a href="{{ route('owner.web.wallet.index') }}" class="inline-flex items-center justify-center rounded-xl border border-current bg-white/70 px-4 py-2 text-sm font-black transition hover:bg-white">
                        Xem ví
                    </a>
                @endif
            </div>
        </div>
    </div>
@endif
