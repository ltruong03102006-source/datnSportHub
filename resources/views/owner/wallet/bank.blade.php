<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thiết lập tài khoản ngân hàng | SportHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <main class="mx-auto max-w-4xl p-6">
        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-2xl font-black text-slate-900">Thiết lập tài khoản ngân hàng</h1>
            <p class="mt-2 text-sm text-slate-500">Lưu thông tin STK của chủ sân để tự động dùng khi tạo yêu cầu rút tiền.</p>

            @if(session('info'))
                <div class="mt-4 rounded-2xl border border-sky-200 bg-sky-50 p-4 text-sm font-semibold text-slate-800">
                    {{ session('info') }}
                </div>
            @endif

            @if(session('success'))
                <div class="mt-4 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('owner.web.wallet.bank.update') }}" class="mt-6 grid gap-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="bank_name" class="mb-2 block text-sm font-semibold text-slate-700">Ngân hàng</label>
                    <select id="bank_name" name="bank_name" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10">
                        <option value="" disabled {{ filled(old('bank_name', $owner->bank_name)) ? '' : 'selected' }}>-- Chọn ngân hàng --</option>
                        @foreach(['Vietcombank', 'Techcombank', 'MBBank', 'VietinBank', 'BIDV', 'ACB', 'VPBank', 'Agribank', 'TPBank', 'Sacombank'] as $bank)
                            <option value="{{ $bank }}" @selected(old('bank_name', $owner->bank_name) === $bank)>{{ $bank }}</option>
                        @endforeach
                    </select>
                    @error('bank_name') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="bank_account_no" class="mb-2 block text-sm font-semibold text-slate-700">Số tài khoản</label>
                    <input id="bank_account_no" name="bank_account_no" value="{{ old('bank_account_no', $owner->bank_account_no) }}" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10" placeholder="VD: 123456789" />
                    @error('bank_account_no') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="bank_account_name" class="mb-2 block text-sm font-semibold text-slate-700">Tên chủ tài khoản</label>
                    <input id="bank_account_name" name="bank_account_name" value="{{ old('bank_account_name', $owner->bank_account_name) }}" type="text" class="w-full rounded-2xl border border-slate-300 bg-white px-4 py-3 text-sm outline-none focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 uppercase" placeholder="VD: NGUYEN VAN A" />
                    @error('bank_account_name') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('owner.web.withdrawals.create') }}" class="inline-flex items-center justify-center rounded-2xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">Quay lại rút tiền</a>
                    <button type="submit" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Lưu STK</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
