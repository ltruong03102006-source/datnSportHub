<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Hợp đồng chuyển nhượng - SportHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation -->
    <nav class="bg-white shadow-sm border-b border-slate-200 px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-500">
                SportHub
            </h1>
            <div class="hidden md:flex items-center gap-2 text-sm text-slate-500 ml-4 border-l border-slate-200 pl-4">
                <a href="{{ route('owner.dashboard') }}" class="hover:text-emerald-600 transition-colors">Dashboard</a>
                <span>/</span>
                <a href="{{ route('owner.web.venues.index') }}" class="hover:text-emerald-600 transition-colors">Quản lý cơ sở</a>
                <span>/</span>
                <span class="text-slate-800 font-medium">Form hợp đồng chuyển nhượng</span>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('owner.dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">Tổng quan</a>
            <a href="{{ route('owner.web.venues.index') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">Danh sách sân</a>
            <a href="{{ route('owner.web.venues.transfers.history') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">Lịch sử chuyển nhượng</a>
            @include('owner.partials.notification-bell')
        </div>
    </nav>

    <!-- Main Content Container -->
    <div class="flex-1 p-6 lg:p-10 max-w-3xl mx-auto w-full">
        <div class="mb-6 flex items-center justify-between">
            <a href="{{ route('owner.web.venues.index') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Quay lại danh sách cơ sở
            </a>
            <a href="{{ route('owner.web.venues.transfers.history') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 flex items-center">
                Lịch sử chuyển nhượng
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <!-- Header Form -->
            <div class="px-8 py-6 border-b border-slate-200 bg-slate-50/80 flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">Form Hợp đồng chuyển nhượng</h2>
                    <p class="text-sm text-slate-500 mt-1">Lập thông tin và khởi tạo hợp đồng chuyển quyền sở hữu cơ sở thể thao</p>
                </div>
                <div class="hidden sm:block text-emerald-600 bg-emerald-50 p-3 rounded-xl border border-emerald-100">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>

            <div class="p-8">
                {{-- Hiển thị thông báo lỗi chung --}}
                @if ($errors->any())
                    <div class="mb-8 p-4 rounded-xl bg-red-50 border border-red-200">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div>
                                <h3 class="text-sm font-semibold text-red-800">Không thể thực hiện yêu cầu:</h3>
                                <ul class="mt-1 text-sm text-red-700 list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('owner.web.venues.transfer.general_store') }}" method="POST" id="transferForm">
                    @csrf

                    <!-- PHẦN 1: THÔNG TIN CƠ SỞ -->
                    <div class="mb-8">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">1</span>
                            <h3 class="text-lg font-bold text-slate-800">Thông tin cơ sở</h3>
                        </div>

                        <div>
                            <label for="venue_id" class="block text-sm font-semibold text-slate-700 mb-2">
                                Chọn cơ sở <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="venue_id" id="venue_id" required
                                        class="w-full appearance-none rounded-xl border-slate-300 bg-white px-4 py-3 text-slate-800 font-medium shadow-sm focus:border-emerald-500 focus:ring-emerald-500 pr-10 border transition-all">
                                    @foreach ($venues as $v)
                                        <option value="{{ $v->id }}" {{ (old('venue_id', $selectedVenueId ?? null) == $v->id) ? 'selected' : '' }}>
                                            ▼ {{ $v->name }} ({{ $v->address ?? 'Chưa cập nhật địa chỉ' }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ĐƯỜNG KẺ PHÂN CÁCH -->
                    <hr class="border-t border-dashed border-slate-200 my-8">

                    <!-- PHẦN 2: THÔNG TIN BÊN NHẬN -->
                    <div class="mb-8">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">2</span>
                            <h3 class="text-lg font-bold text-slate-800">Thông tin bên nhận</h3>
                        </div>

                        <div class="mb-4">
                            <label for="receiver_email" class="block text-sm font-semibold text-slate-700 mb-2">
                                Email bên nhận <span class="text-red-500">*</span>
                            </label>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <input type="email" name="receiver_email" id="receiver_email" value="{{ old('receiver_email') }}" required
                                       class="flex-1 rounded-xl border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3 text-slate-800 border"
                                       placeholder="Nhập email tài khoản Chủ sân bên nhận...">
                                
                                <button type="button" id="btn_search_receiver"
                                        class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow-sm transition-colors flex items-center justify-center gap-2 whitespace-nowrap">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                    Tìm người nhận
                                </button>
                            </div>
                        </div>

                        <!-- KHU VỰC HIỂN THỊ KẾT QUẢ KIỂM TRA EMAIL -->
                        <div id="email_result" class="min-h-[24px]">
                            @if(old('receiver_email'))
                                <span class="text-xs text-slate-500">Vui lòng bấm nút <strong>[Tìm người nhận]</strong> để xác thực tài khoản.</span>
                            @else
                                <span class="text-xs text-slate-500">Nhập email và bấm nút <strong>[Tìm người nhận]</strong> để hệ thống xác minh tính hợp pháp của tài khoản nhận.</span>
                            @endif
                        </div>
                    </div>

                    <!-- ĐƯỜNG KẺ PHÂN CÁCH -->
                    <hr class="border-t border-dashed border-slate-200 my-8">

                    <!-- PHẦN 3: THÔNG TIN CHUYỂN NHƯỢNG -->
                    <div class="mb-8">
                        <div class="flex items-center gap-2 mb-4">
                            <span class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs">3</span>
                            <h3 class="text-lg font-bold text-slate-800">Thông tin chuyển nhượng</h3>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Giá chuyển nhượng -->
                            <div class="md:col-span-2">
                                <label for="price" class="block text-sm font-semibold text-slate-700 mb-2">
                                    Giá chuyển nhượng (VNĐ) <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="number" step="1000" min="0" name="price" id="price" value="{{ old('price') }}" required
                                           class="w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3 pl-4 pr-16 text-slate-800 border font-medium"
                                           placeholder="Ví dụ: 50000000">
                                    <span class="absolute inset-y-0 right-0 flex items-center pr-4 text-sm font-semibold text-slate-500 pointer-events-none">VNĐ</span>
                                </div>
                            </div>

                            <!-- Ngày tạo hợp đồng -->
                            <div>
                                <label for="contract_date" class="block text-sm font-semibold text-slate-700 mb-2">
                                    Ngày tạo hợp đồng <span class="text-red-500">*</span>
                                </label>
                                <input type="date" name="contract_date" id="contract_date" value="{{ old('contract_date', date('Y-m-d')) }}" required
                                       class="w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3 text-slate-800 border font-medium">
                            </div>

                            <!-- Địa điểm lập hợp đồng -->
                            <div>
                                <label for="contract_location" class="block text-sm font-semibold text-slate-700 mb-2">
                                    Địa điểm lập hợp đồng <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="contract_location" id="contract_location" value="{{ old('contract_location', 'Thành phố Hồ Chí Minh') }}" required
                                       class="w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 px-4 py-3 text-slate-800 border"
                                       placeholder="Ví dụ: Thành phố Hồ Chí Minh, Việt Nam">
                            </div>
                        </div>
                    </div>

                    <!-- GHI CHÚ VÀ LƯU Ý -->
                    <div class="bg-amber-50/80 rounded-xl p-4 mb-8 border border-amber-200/80">
                        <h4 class="text-sm font-bold text-amber-800 flex items-center mb-2">
                            <svg class="w-4 h-4 mr-2 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Lưu ý quy trình chuyển nhượng hợp đồng
                        </h4>
                        <ul class="text-xs text-amber-700 list-disc list-inside space-y-1 leading-relaxed">
                            <li>Sau khi gửi, bên nhận sẽ nhận được thông báo để xác nhận và nộp hồ sơ pháp lý.</li>
                            <li>Admin SportHub sẽ kiểm duyệt tính hợp pháp của thông tin trước khi hoàn tất chuyển nhượng.</li>
                        </ul>
                    </div>

                    <!-- ACTION BUTTONS -->
                    <div class="flex flex-col-reverse sm:flex-row justify-end gap-3">
                        <a href="{{ route('owner.web.venues.index') }}" 
                           class="px-6 py-3 text-sm font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors text-center">
                            Hủy
                        </a>
                        <button type="submit" 
                                class="px-8 py-3 text-sm font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 rounded-xl shadow-md hover:shadow-lg transition-all text-center flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Tạo hợp đồng chuyển nhượng
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('owner.partials.notification-script')

    <!-- JavaScript Xử Lý [Tìm người nhận] AJAX -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('receiver_email');
            const searchBtn = document.getElementById('btn_search_receiver');
            const resultDiv = document.getElementById('email_result');

            function performSearch() {
                const email = emailInput.value.trim();

                if (!email) {
                    resultDiv.innerHTML = '<span class="text-xs text-red-500 font-medium flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> Vui lòng nhập Email để tìm người nhận.</span>';
                    return;
                }

                resultDiv.innerHTML = `
                    <span class="text-xs text-slate-500 flex items-center py-1">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Đang truy vấn hệ thống...
                    </span>`;

                fetch('{{ route('owner.web.venues.transfer.check-email') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ email: email })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        resultDiv.innerHTML = `
                            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs flex items-center justify-between gap-2 shadow-sm animate-fade-in">
                                <div class="flex items-center gap-2">
                                    <span class="bg-emerald-600 text-white p-1 rounded-full shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </span>
                                    <div>
                                        <div class="font-bold text-emerald-900 text-sm flex items-center gap-2">
                                            <span>Email tồn tại - Hợp pháp</span>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-emerald-200 text-emerald-800">
                                                Hợp pháp
                                            </span>
                                        </div>
                                        <div class="mt-0.5 text-emerald-700">Chủ sân: <strong>${data.name}</strong> (${data.email})</div>
                                    </div>
                                </div>
                            </div>`;
                    } else {
                        resultDiv.innerHTML = `
                            <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs flex items-center gap-2 shadow-sm">
                                <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                <span>${data.message}</span>
                            </div>`;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = '<div class="p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs">Lỗi kết nối khi tìm kiếm người nhận.</div>';
                });
            }

            searchBtn.addEventListener('click', performSearch);

            // Bấm Enter ở ô Email sẽ gọi nút Tìm kiếm thay vì submit form ngay
            emailInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performSearch();
                }
            });

            // Tự động check nếu đã có giá trị sẵn khi load trang
            if (emailInput.value.trim() !== '') {
                performSearch();
            }
        });
    </script>
</body>
</html>