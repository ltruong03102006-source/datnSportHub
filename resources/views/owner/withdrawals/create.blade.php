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
                      class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                      id="withdrawForm">
                    @csrf

                    <!-- 1. SỐ TIỀN & NÚT RÚT TOÀN BỘ -->
                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label for="amount" class="text-sm font-bold text-slate-900">
                                Số tiền muốn rút
                            </label>
                            <!-- Nút Rút toàn bộ -->
                            <button type="button" 
                                    onclick="document.getElementById('amount').value = {{ (int) $availableBalance }}; document.getElementById('amount').dispatchEvent(new Event('input'));" 
                                    class="rounded-lg bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-700 transition hover:bg-emerald-200"
                                    @disabled($availableBalance <= 0)>
                                Rút toàn bộ
                            </button>
                        </div>
                        
                       <div class="relative">
    <input type="number"
           id="amount"
           name="amount"
           min="{{ $minWithdraw }}"
           max="{{ (int) $availableBalance }}"
           step="1000"
           value="{{ old('amount') }}"
           placeholder="Ví dụ: {{ $minWithdraw }}"
           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
           @disabled($availableBalance <= 0)
           required>
</div>
                        
                        <!-- Hiển thị Real-time & Hạn mức -->
                        <!-- Hiển thị Real-time & Hạn mức -->
<div class="mt-2 flex items-center justify-between">
    <p class="text-xs font-semibold text-slate-500">Tối thiểu: <span class="text-red-500">{{ number_format($minWithdraw, 0, ',', '.') }}đ</span></p>
    <p class="text-xs font-black text-emerald-600" id="amount-format-hint"></p>
</div>
                        @error('amount')
                            <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- 2. NGÂN HÀNG & SỐ TÀI KHOẢN -->
                    <div class="mt-5 grid gap-5 md:grid-cols-2">
                        @if($bankConfigured)
                            <div class="space-y-3">
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Ngân hàng</p>
                                    <p class="mt-2 font-bold text-slate-900">{{ $owner->bank_name }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Số tài khoản</p>
                                    <p class="mt-2 font-bold text-slate-900">{{ $owner->bank_account_no }}</p>
                                </div>
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Chủ tài khoản</p>
                                    <p class="mt-2 font-bold text-slate-900">{{ $owner->bank_account_name }}</p>
                                </div>
                                <div class="mt-4">
                                    <a href="{{ route('owner.web.wallet.bank.edit') }}" class="inline-flex items-center justify-center rounded-2xl border border-emerald-600 bg-white px-5 py-3 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">
                                        Thay đổi STK khác
                                    </a>
                                </div>
                            </div>
                        @else
                            <div>
                                <label for="bank_name" class="mb-2 block text-sm font-bold text-slate-900">
                                    Ngân hàng
                                </label>
                                <select id="bank_name"
                                       name="bank_name"
                                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm font-bold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
                                       @disabled($availableBalance <= 0)
                                       required>
                                    <option value="" disabled selected>-- Chọn ngân hàng --</option>
                                    <option value="Vietcombank" @selected(old('bank_name') == 'Vietcombank')>Vietcombank (VCB)</option>
                                    <option value="Techcombank" @selected(old('bank_name') == 'Techcombank')>Techcombank (TCB)</option>
                                    <option value="MBBank" @selected(old('bank_name') == 'MBBank')>MBBank (MB)</option>
                                    <option value="VietinBank" @selected(old('bank_name') == 'VietinBank')>VietinBank (CTG)</option>
                                    <option value="BIDV" @selected(old('bank_name') == 'BIDV')>BIDV</option>
                                    <option value="ACB" @selected(old('bank_name') == 'ACB')>ACB</option>
                                    <option value="VPBank" @selected(old('bank_name') == 'VPBank')>VPBank (VPB)</option>
                                    <option value="Agribank" @selected(old('bank_name') == 'Agribank')>Agribank</option>
                                    <option value="TPBank" @selected(old('bank_name') == 'TPBank')>TPBank (TPB)</option>
                                    <option value="Sacombank" @selected(old('bank_name') == 'Sacombank')>Sacombank (STB)</option>
                                </select>
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

                            <div class="md:col-span-2">
                                <label for="bank_account_holder" class="mb-2 block text-sm font-bold text-slate-900">
                                    Chủ tài khoản
                                </label>
                                <input type="text"
                                       id="bank_account_holder"
                                       name="bank_account_holder"
                                       value="{{ old('bank_account_holder') }}"
                                       placeholder="Ví dụ: NGUYEN VAN A"
                                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm font-bold text-slate-900 uppercase outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
                                       @disabled($availableBalance <= 0)
                                       required>
                                @error('bank_account_holder')
                                    <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>

                    @unless($bankConfigured)
                        <div class="mt-4 flex items-center gap-2">
                            <input type="checkbox" id="save_bank_info" name="save_bank_info" class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" @checked(old('save_bank_info'))>
                            <label for="save_bank_info" class="text-sm font-semibold text-slate-600 cursor-pointer">
                                Lưu thông tin nhận tiền cho lần rút sau
                            </label>
                        </div>
                    @endunless

                    <div class="mt-5">
                        <label for="owner_note" class="mb-2 block text-sm font-bold text-slate-900">
                            Ghi chú
                        </label>
                        <textarea id="owner_note"
                                  name="owner_note"
                                  rows="3"
                                  placeholder="Nhập ghi chú nếu có"
                                  class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10"
                                  @disabled($availableBalance <= 0)>{{ old('owner_note') }}</textarea>
                        @error('owner_note')
                            <p class="mt-2 text-sm font-bold text-red-600">{{ $message }}</p>
                        @enderror
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
    <!-- CHÈN THÊM ĐOẠN SCRIPT NÀY VÀO ĐÂY -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Format tiền Real-time khi gõ
            const amountInput = document.getElementById('amount');
            const amountHint = document.getElementById('amount-format-hint');
            
            if(amountInput && amountHint) {
                amountInput.addEventListener('input', function(e) {
                    const val = parseInt(e.target.value);
                    if (!isNaN(val) && val > 0) {
                        amountHint.innerText = 'Thực nhận: ' + new Intl.NumberFormat('vi-VN').format(val) + 'đ';
                    } else {
                        amountHint.innerText = '';
                    }
                });
            }

            // 2. Logic Lưu & Tự động điền tài khoản (LocalStorage)
            const bankName = document.getElementById('bank_name');
            const accNum = document.getElementById('bank_account_number');
            const accHolder = document.getElementById('bank_account_holder');
            const saveCheckbox = document.getElementById('save_bank_info');
            const form = document.getElementById('withdrawForm');

            if(bankName && accNum && accHolder && saveCheckbox && form) {
                // Lấy data từ bộ nhớ tạm của trình duyệt lúc load trang
                const savedBankInfo = localStorage.getItem('sh_saved_bank');
                if (savedBankInfo) {
                    const saved = JSON.parse(savedBankInfo);
                    // Chỉ tự điền nếu các ô đang trống
                    if (!bankName.value) bankName.value = saved.bank_name;
                    if (!accNum.value) accNum.value = saved.account_number;
                    if (!accHolder.value) accHolder.value = saved.account_holder;
                    saveCheckbox.checked = true;
                }

                // Lưu data vào bộ nhớ trình duyệt khi Submit form
                form.addEventListener('submit', function() {
                    if (saveCheckbox.checked) {
                        localStorage.setItem('sh_saved_bank', JSON.stringify({
                            bank_name: bankName.value,
                            account_number: accNum.value,
                            account_holder: accHolder.value
                        }));
                    } else {
                        // Nếu bỏ tick thì xóa luôn
                        localStorage.removeItem('sh_saved_bank');
                    }
                });
            }
        });
    </script>
</body>
</html>
