<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nạp tiền vào ví | SportHub</title>
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
                <span class="font-bold text-slate-800">Nạp tiền vào ví</span>
            </div>
        </div>

        <div class="flex items-center gap-5">
            <a href="{{ route('owner.dashboard') }}" class="text-sm font-semibold text-slate-600 hover:text-emerald-600">Tổng quan</a>
            <a href="{{ route('owner.web.calendar.index') }}" class="text-sm font-semibold text-slate-600 hover:text-emerald-600">Lịch đặt sân</a>
            <a href="{{ route('owner.web.packages.index') }}" class="text-sm font-semibold text-slate-600 hover:text-emerald-600">Quản lý gói</a>
            @include('owner.partials.notification-bell')
        </div>
    </nav>

    <main class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('owner.dashboard') }}"
               class="text-sm font-bold text-emerald-700 hover:text-emerald-800">
                ← Quay lại tổng quan
            </a>

            <h1 class="mt-3 text-3xl font-black text-slate-900">
                Nạp tiền vào ví
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                Nạp tiền để thanh toán công nợ hoặc tăng số dư ví chủ sân.
            </p>
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

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="lg:col-span-1">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">
                        Số dư ví
                    </p>

                    <p class="mt-2 text-2xl font-black {{ $wallet->balance < 0 ? 'text-red-600' : 'text-emerald-700' }}">
                        {{ number_format($wallet->balance, 0, ',', '.') }}đ
                    </p>

                    <div class="mt-5 rounded-xl bg-slate-50 p-4">
                        <p class="text-xs font-bold uppercase text-slate-400">
                            Công nợ hiện tại
                        </p>

                        <p class="mt-1 text-lg font-black {{ ($debtAmount ?? 0) > 0 ? 'text-red-600' : 'text-slate-700' }}">
                            {{ number_format($debtAmount ?? 0, 0, ',', '.') }}đ
                        </p>
                    </div>

                    @if(isset($wallet->credit_limit))
                        <div class="mt-3 rounded-xl bg-amber-50 p-4">
                            <p class="text-xs font-bold uppercase text-amber-600">
                                Hạn mức nợ
                            </p>

                            <p class="mt-1 text-lg font-black text-amber-700">
                                {{ number_format($wallet->credit_limit, 0, ',', '.') }}đ
                            </p>
                        </div>
                    @endif

                    @if(isset($wallet->status))
                        <div class="mt-3 rounded-xl bg-emerald-50 p-4">
                            <p class="text-xs font-bold uppercase text-emerald-600">
                                Trạng thái ví
                            </p>

                            <p class="mt-1 text-sm font-black text-emerald-700">
                                {{ $wallet->status === 'active' ? 'Đang hoạt động' : $wallet->status }}
                            </p>
                        </div>
                    @endif
                </div>
            </section>

            <section class="lg:col-span-2">
                <form method="POST"
                      action="{{ route('owner.web.wallet.topup.store') }}"
                      class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    @csrf

                    <div>
                        <label for="amount" class="mb-2 block text-sm font-bold text-slate-900">
                            Số tiền muốn nạp
                        </label>

                        <input type="number"
                               id="amount"
                               name="amount"
                               min="10000"
                               max="50000000"
                               step="1000"
                               value="{{ old('amount') }}"
                               placeholder="Ví dụ: 500000"
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
                               required>

                        @error('amount')
                            <p class="mt-2 text-sm font-bold text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <input type="hidden" name="payment_method" value="vnpay">

                    <div class="mt-5">
                        <p class="mb-3 text-sm font-bold text-slate-900">
                            Chọn nhanh
                        </p>

                        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                            @foreach([100000, 200000, 500000, 1000000, 2000000] as $quickAmount)
                                <button type="button"
                                        data-amount="{{ $quickAmount }}"
                                        class="quick-amount rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-black text-slate-700 transition hover:border-emerald-400 hover:bg-emerald-50 hover:text-emerald-700">
                                    {{ number_format($quickAmount, 0, ',', '.') }}đ
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-6 rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-700">
                        <p class="font-bold">
                            Phương thức thanh toán: VNPay
                        </p>

                        <p class="mt-1">
                            Sau khi bấm thanh toán, bạn sẽ được chuyển sang cổng VNPay. Số dư ví chỉ được cập nhật sau khi giao dịch thành công.
                        </p>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <a href="{{ route('owner.dashboard') }}"
                           class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50">
                            Hủy
                        </a>

                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-emerald-700">
                            Thanh toán qua VNPay
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </main>

    <script>
        document.querySelectorAll('.quick-amount').forEach((button) => {
            button.addEventListener('click', () => {
                const amountInput = document.getElementById('amount');
                amountInput.value = button.dataset.amount;
                amountInput.focus();
            });
        });
    </script>
    @include('owner.partials.notification-script')
</body>
</html>
