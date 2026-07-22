<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo yêu cầu rút tiền | SportHub</title>
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
                <a href="{{ route('owner.web.withdrawals.index') }}" class="font-semibold hover:text-emerald-600">Yêu cầu rút tiền</a>
                <span>/</span>
                <span class="font-bold text-slate-800">Tạo yêu cầu</span>
            </div>
        </div>

        <div class="flex items-center gap-5">
            <a href="{{ route('owner.dashboard') }}" class="text-sm font-semibold text-slate-600 hover:text-emerald-600">Tổng quan</a>
            <a href="{{ route('owner.web.calendar.index') }}" class="text-sm font-semibold text-slate-600 hover:text-emerald-600">Lịch đặt sân</a>
            <a href="{{ route('owner.web.packages.index') }}" class="text-sm font-semibold text-slate-600 hover:text-emerald-600">Quản lý gói</a>
            @include('owner.partials.notification-bell')
        </div>
    </nav>

    <main class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-6">
            <a href="{{ route('owner.web.withdrawals.index') }}"
               class="text-sm font-bold text-emerald-700 hover:text-emerald-800">
                ← Quay lại danh sách yêu cầu rút tiền
            </a>

            <h1 class="mt-3 text-3xl font-black text-slate-900">
                Tạo yêu cầu rút tiền
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                Gửi thông tin rút tiền để admin kiểm tra và duyệt. Số dư ví chưa bị trừ ở bước này.
            </p>
        </div>

        @if(session('error'))
            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-700">
                {{ session('error') }}
            </div>
        @endif

        @if($errors->has('wallet'))
            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-700">
                {{ $errors->first('wallet') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-3">
            <section class="lg:col-span-1">
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-xs font-black uppercase tracking-wider text-slate-400">Thông tin ví</p>

                    <div class="mt-5 space-y-3">
                        <div class="rounded-xl bg-slate-50 p-4">
                            <p class="text-xs font-bold uppercase text-slate-400">Số dư ví</p>
                            <p class="mt-1 text-xl font-black {{ $wallet->balance < 0 ? 'text-red-600' : 'text-emerald-700' }}">
                                {{ number_format($wallet->balance, 0, ',', '.') }}đ
                            </p>
                        </div>

                        <div class="rounded-xl bg-amber-50 p-4">
                            <p class="text-xs font-bold uppercase text-amber-600">Đang chờ rút</p>
                            <p class="mt-1 text-xl font-black text-amber-700">
                                {{ number_format($pendingWithdrawAmount, 0, ',', '.') }}đ
                            </p>
                        </div>

                        <div class="rounded-xl bg-emerald-50 p-4">
                            <p class="text-xs font-bold uppercase text-emerald-600">Số dư khả dụng</p>
                            <p class="mt-1 text-xl font-black text-emerald-700">
                                {{ number_format($availableBalance, 0, ',', '.') }}đ
                            </p>
                        </div>
                    </div>

                    @if($availableBalance <= 0)
                        <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-700">
                            Bạn không có số dư khả dụng để rút tiền.
                        </div>
                    @endif
                </div>
            </section>

            <section class="lg:col-span-2">
                <form method="POST"
                      action="{{ route('owner.web.withdrawals.store') }}"
                      class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    @csrf

                    <div>
                        <label for="amount" class="mb-2 block text-sm font-bold text-slate-900">
                            Số tiền muốn rút
                        </label>
                        <input type="number"
                               id="amount"
                               name="amount"
                               min="50000"
                               max="{{ (int) $availableBalance }}"
                               step="1000"
                               value="{{ old('amount') }}"
                               placeholder="Ví dụ: 1000000"
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
                               @disabled($availableBalance <= 0)
                               required>
                        @error('amount')
                            <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        <div>
                            <label for="bank_name" class="mb-2 block text-sm font-bold text-slate-900">
                                Tên ngân hàng
                            </label>
                            <input type="text"
                                   id="bank_name"
                                   name="bank_name"
                                   value="{{ old('bank_name') }}"
                                   placeholder="Ví dụ: Vietcombank"
                                   class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
                                   @disabled($availableBalance <= 0)
                                   required>
                            @error('bank_name')
                                <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="bank_account_number" class="mb-2 block text-sm font-bold text-slate-900">
                                Số tài khoản
                            </label>
                            <input type="text"
                                   id="bank_account_number"
                                   name="bank_account_number"
                                   value="{{ old('bank_account_number') }}"
                                   placeholder="Ví dụ: 123456789"
                                   class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
                                   @disabled($availableBalance <= 0)
                                   required>
                            @error('bank_account_number')
                                <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-5">
                        <label for="bank_account_holder" class="mb-2 block text-sm font-bold text-slate-900">
                            Chủ tài khoản
                        </label>
                        <input type="text"
                               id="bank_account_holder"
                               name="bank_account_holder"
                               value="{{ old('bank_account_holder') }}"
                               placeholder="Ví dụ: NGUYEN VAN A"
                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
                               @disabled($availableBalance <= 0)
                               required>
                        @error('bank_account_holder')
                            <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-5">
                        <label for="owner_note" class="mb-2 block text-sm font-bold text-slate-900">
                            Ghi chú
                        </label>
                        <textarea id="owner_note"
                                  name="owner_note"
                                  rows="4"
                                  placeholder="Nhập ghi chú nếu có"
                                  class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
                                  @disabled($availableBalance <= 0)>{{ old('owner_note') }}</textarea>
                        @error('owner_note')
                            <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-6 rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-700">
                        <p class="font-bold">Lưu ý</p>
                        <p class="mt-1">
                            Yêu cầu rút tiền sẽ ở trạng thái chờ duyệt. Số dư ví chỉ được xử lý khi admin duyệt ở bước sau.
                        </p>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <a href="{{ route('owner.web.withdrawals.index') }}"
                           class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-extrabold text-slate-700 transition hover:bg-slate-50">
                            Hủy
                        </a>

                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-5 py-3 text-sm font-extrabold text-white transition hover:bg-emerald-700 disabled:cursor-not-allowed disabled:bg-slate-300"
                                @disabled($availableBalance <= 0)>
                            Gửi yêu cầu
                        </button>
                    </div>
                </form>
            </section>
        </div>
    </main>

    @include('owner.partials.notification-script')
</body>
</html>
