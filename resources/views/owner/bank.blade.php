<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cấu hình Ngân hàng - SportHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    <style>
        :root {
            --owner-ink: #172033;
            --owner-panel: rgba(255, 255, 255, 0.84);
            --owner-line: rgba(255, 255, 255, 0.58);
        }

        body {
            font-family: 'Inter', sans-serif;
            background:
                linear-gradient(115deg, rgba(16, 185, 129, 0.16) 0%, transparent 28%),
                linear-gradient(245deg, rgba(59, 130, 246, 0.14) 0%, transparent 34%),
                linear-gradient(135deg, #f7fee7 0%, #ecfeff 36%, #fdf2f8 72%, #fff7ed 100%);
            color: var(--owner-ink);
        }

        body::before {
            content: "";
            position: fixed;
            inset: 0;
            pointer-events: none;
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.42) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, 0.36) 1px, transparent 1px);
            background-size: 42px 42px;
            mask-image: linear-gradient(to bottom, rgba(0, 0, 0, 0.78), transparent 82%);
        }

        .owner-topbar {
            background: linear-gradient(120deg, rgba(15, 23, 42, 0.92), rgba(5, 150, 105, 0.88), rgba(37, 99, 235, 0.86));
            border-bottom: 1px solid rgba(255, 255, 255, 0.24);
            box-shadow: 0 18px 50px rgba(15, 23, 42, 0.22);
        }

        .owner-topbar a,
        .owner-topbar h1 {
            color: #fff !important;
        }

        .owner-pill {
            border-radius: 999px;
            padding: 0.55rem 0.9rem;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.18);
            transition: 0.2s ease;
        }

        .owner-pill:hover {
            background: rgba(255, 255, 255, 0.24);
            transform: translateY(-1px);
        }

        .owner-page {
            position: relative;
            z-index: 1;
        }

        .hero-shell {
            background:
                linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(240, 253, 250, 0.78)),
                linear-gradient(120deg, rgba(16, 185, 129, 0.18), rgba(59, 130, 246, 0.12), rgba(244, 114, 182, 0.12));
            backdrop-filter: blur(18px) saturate(145%);
            -webkit-backdrop-filter: blur(18px) saturate(145%);
            border: 1px solid var(--owner-line);
            box-shadow: 0 22px 60px rgba(15, 23, 42, 0.13);
        }

        .glass-card {
            background: var(--owner-panel);
            backdrop-filter: blur(18px) saturate(145%);
            -webkit-backdrop-filter: blur(18px) saturate(145%);
            border: 1px solid var(--owner-line);
            box-shadow: 0 24px 65px rgba(15, 23, 42, 0.14);
            transition: all 0.3s ease;
        }

        .field-shell {
            background: rgba(255, 255, 255, 0.88);
            border: 1px solid rgba(148, 163, 184, 0.34);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.86);
        }

        .qr-shell {
            background:
                radial-gradient(circle at top right, rgba(16, 185, 129, 0.16), transparent 36%),
                radial-gradient(circle at bottom left, rgba(59, 130, 246, 0.13), transparent 36%),
                rgba(255, 255, 255, 0.78);
            border: 1px dashed rgba(16, 185, 129, 0.34);
        }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation -->
    <nav class="owner-topbar px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold">
                Thanh toán (Bank)
            </h1>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('owner.dashboard') }}" class="owner-pill text-sm font-medium">Dashboard</a>
            <a href="{{ route('owner.web.venues.index') }}" class="owner-pill text-sm font-medium">Quản lý cơ sở</a>
            <a href="{{ route('owner.web.calendar.index') }}" class="owner-pill text-sm font-medium">Lịch đặt sân</a>
            <a href="{{ route('owner.web.packages.index') }}" class="owner-pill text-sm font-medium">Quản lý gói</a>
        </div>
    </nav>

    <div class="owner-page flex-1 p-6 lg:p-10 max-w-5xl mx-auto w-full">
        <div class="hero-shell mb-8 rounded-3xl p-6 md:p-8">
            <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-emerald-600">
                SportHub Owner
            </p>
            <h2 class="mt-2 text-3xl font-extrabold text-slate-950 md:text-4xl">Cấu hình thanh toán</h2>
            <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-600">
                Thiết lập tài khoản nhận tiền mặc định để hệ thống tạo mã VietQR khi khách thanh toán đặt sân.
            </p>
        </div>

        @if (session('success'))
            <div class="mb-6 flex items-center gap-2 rounded-2xl border border-emerald-200 bg-emerald-50/90 px-4 py-3 text-sm font-semibold text-emerald-800 shadow-sm">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="glass-card rounded-3xl p-6 sm:p-8"
            x-data="{ 
                banks: [], 
                selectedBank: '{{ old('bank_name', Auth::user()->bank_name) }}',
                accountNo: '{{ old('bank_account_no', Auth::user()->bank_account_no) }}',
                accountName: '{{ old('bank_account_name', Auth::user()->bank_account_name) }}',
                qrUrl: '',
                fetchBanks() {
                    fetch('https://api.vietqr.io/v2/banks')
                        .then(res => res.json())
                        .then(data => {
                            if(data.code === '00') {
                                this.banks = data.data;
                            }
                        });
                },
                generatePreview() {
                    if (this.selectedBank && this.accountNo) {
                        const name = this.accountName ? this.accountName.toUpperCase().trim() : 'CHU SAN';
                        const addInfo = 'THANH TOAN TEST';
                        this.qrUrl = `https://img.vietqr.io/image/${this.selectedBank}-${this.accountNo}-compact2.png?amount=50000&addInfo=${encodeURIComponent(addInfo)}&accountName=${encodeURIComponent(name)}`;
                    } else {
                        this.qrUrl = '';
                    }
                }
            }"
            x-init="fetchBanks(); generatePreview(); $watch('selectedBank', () => generatePreview()); $watch('accountNo', () => generatePreview()); $watch('accountName', () => generatePreview())"
        >
            <div class="flex flex-col lg:flex-row gap-10">
                <form method="POST" action="{{ route('account.profile.bank') }}" class="flex-1 space-y-6">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label for="bank_name" class="block text-sm font-semibold text-slate-700 mb-2">Ngân hàng</label>
                        <select id="bank_name" name="bank_name" x-model="selectedBank"
                            class="field-shell w-full rounded-2xl px-4 py-3 text-sm outline-none focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-600/10 transition">
                            <option value="">-- Chọn ngân hàng --</option>
                            <template x-for="bank in banks" :key="bank.id">
                                <option :value="bank.bin" x-text="`${bank.shortName} - ${bank.name}`" :selected="bank.bin == selectedBank || bank.shortName == selectedBank"></option>
                            </template>
                        </select>
                        @error('bank_name') <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label for="bank_account_no" class="block text-sm font-semibold text-slate-700 mb-2">Số tài khoản</label>
                        <input id="bank_account_no" name="bank_account_no" type="text" x-model="accountNo" placeholder="VD: 0123456789"
                            class="field-shell w-full rounded-2xl px-4 py-3 text-sm outline-none focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-600/10 transition">
                        @error('bank_account_no') <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    
                    <div>
                        <label for="bank_account_name" class="block text-sm font-semibold text-slate-700 mb-2">Tên chủ tài khoản</label>
                        <input id="bank_account_name" name="bank_account_name" type="text" x-model="accountName" placeholder="VD: NGUYEN VAN A"
                            class="field-shell w-full rounded-2xl px-4 py-3 text-sm outline-none focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-600/10 uppercase transition">
                        @error('bank_account_name') <p class="mt-2 text-xs text-red-600 font-medium">{{ $message }}</p> @enderror
                    </div>
                    
                    <div class="pt-4 border-t border-white/70">
                        <button type="submit" class="w-full sm:w-auto rounded-full bg-emerald-600 px-8 py-3 text-sm font-bold text-white shadow hover:bg-emerald-700 transition">
                            Lưu cấu hình
                        </button>
                    </div>
                </form>

                <!-- Xem trước QR -->
                <div class="qr-shell shrink-0 flex flex-col items-center justify-center p-6 rounded-3xl w-full lg:w-80 shadow-sm relative">
                    <div class="absolute top-0 right-0 p-3 opacity-10">
                        <svg class="w-16 h-16 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M4 4h6v6H4zm2 2v2h2V6zM4 14h6v6H4zm2 2v2h2v-2zM14 4h6v6h-6zm2 2v2h2V6z"/><path d="M14 14h2v2h-2zm4 0h2v2h-2zm-2 2h2v2h-2zm4 0h2v2h-2zm-2 2h2v2h-2zm4 0h2v2h-2zm0-4h2v2h-2z"/></svg>
                    </div>

                    <h3 class="text-sm font-bold text-slate-500 mb-4 uppercase tracking-wider">Xem trước QR Test</h3>
                    <div class="w-56 h-56 bg-white rounded-2xl border border-slate-100 flex items-center justify-center shadow-inner overflow-hidden mb-4 relative z-10">
                        <template x-if="qrUrl">
                            <img :src="qrUrl" alt="QR Preview" class="w-full h-full object-contain p-2">
                        </template>
                        <template x-if="!qrUrl">
                            <div class="text-center p-4">
                                <svg class="w-12 h-12 text-slate-200 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"></path></svg>
                                <span class="text-xs text-slate-400">Nhập đủ thông tin bên trái để xem trước mã QR</span>
                            </div>
                        </template>
                    </div>
                    
                    <template x-if="qrUrl">
                        <div class="text-center bg-white/75 rounded-2xl p-3 w-full border border-white/80 z-10">
                            <p class="text-xs text-slate-500 leading-relaxed">
                                Dùng App Ngân hàng quét thử với số tiền <strong class="text-emerald-600">50.000đ</strong> để kiểm tra kết quả.
                            </p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
