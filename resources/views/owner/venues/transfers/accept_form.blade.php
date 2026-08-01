<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhận chuyển nhượng cơ sở - SportHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
    </style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col">
    
    <!-- Top Navigation (Giống các trang khác) -->
    <nav class="bg-white shadow-sm border-b border-slate-200 px-6 py-4 flex justify-between items-center sticky top-0 z-50">
        <div class="flex items-center gap-4">
            <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-600 to-teal-500">
                SportHub
            </h1>
            <div class="hidden md:flex items-center gap-2 text-sm text-slate-500 ml-4 border-l border-slate-200 pl-4">
                <a href="{{ route('owner.dashboard') }}" class="hover:text-emerald-600 transition-colors">Dashboard</a>
                <span>/</span>
                <span class="text-slate-800 font-medium">Nhận chuyển nhượng</span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex-1 p-6 lg:p-10 max-w-5xl mx-auto w-full">
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            
            <!-- Header -->
            <div class="bg-emerald-50 border-b border-emerald-100 p-6">
                <h2 class="text-2xl font-bold text-emerald-800">Hoàn tất nhận chuyển nhượng</h2>
                <p class="text-emerald-700 mt-1">Cơ sở: <strong class="text-emerald-900">{{ $transfer->venue->name }}</strong></p>
            </div>
            
            <div class="p-6 lg:p-8">
                <!-- Thông tin Hợp đồng Chuyển nhượng -->
                <div class="mb-6 p-5 bg-emerald-50/50 border border-emerald-200 rounded-xl">
                    <h3 class="text-sm font-bold text-emerald-900 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Thông tin Hợp đồng Chuyển nhượng
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                        <div class="bg-white p-3 rounded-lg border border-emerald-100">
                            <span class="text-slate-500 block">Giá chuyển nhượng</span>
                            <span class="text-emerald-700 font-bold text-base">{{ $transfer->price ? number_format($transfer->price, 0, ',', '.') . ' VNĐ' : 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="bg-white p-3 rounded-lg border border-emerald-100">
                            <span class="text-slate-500 block">Ngày tạo hợp đồng</span>
                            <span class="text-slate-800 font-semibold text-sm">{{ $transfer->contract_date ? \Carbon\Carbon::parse($transfer->contract_date)->format('d/m/Y') : 'Chưa cập nhật' }}</span>
                        </div>
                        <div class="bg-white p-3 rounded-lg border border-emerald-100">
                            <span class="text-slate-500 block">Địa điểm lập hợp đồng</span>
                            <span class="text-slate-800 font-semibold text-sm">{{ $transfer->contract_location ?? 'Chưa cập nhật' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Cảnh báo -->
                <div class="mb-8 p-4 bg-blue-50 border border-blue-100 rounded-xl flex items-start">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p class="text-sm text-blue-800 leading-relaxed">
                        Để chính thức đứng tên cơ sở này, bạn vui lòng cung cấp thông tin liên hệ và hồ sơ pháp lý. <br>
                        <strong>Lưu ý:</strong> Hệ thống sẽ tự động sang tên cho bạn ngay khi Admin phê duyệt.
                    </p>
                </div>

                <!-- Form Nhập liệu -->
                <form action="{{ route('owner.web.venues.transfers.accept.submit', $transfer->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                    @csrf
                    
                    <!-- Phần 1: Thông tin liên hệ -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-200 pb-2">1. Thông tin liên hệ cơ sở (Dành cho khách hàng)</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">SĐT Hotline Sân <span class="text-red-500">*</span></label>
                                <input type="text" name="phone" value="{{ old('phone') }}" required oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="15" class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition-colors @error('phone') border-red-500 @enderror">
                                @error('phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Email Sân <span class="text-red-500">*</span></label>
                                <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition-colors @error('email') border-red-500 @enderror">
                                @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Phần 2: Hồ sơ pháp lý -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 mb-4 border-b border-slate-200 pb-2 mt-2">2. Hồ sơ pháp lý & Thanh toán</h3>
                        
                        <!-- Thông tin cá nhân & GPKD -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tên chủ sở hữu <span class="text-red-500">*</span></label>
                                <input type="text" name="owner_name" value="{{ old('owner_name') }}" required class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition-colors @error('owner_name') border-red-500 @enderror">
                                @error('owner_name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Số CCCD <span class="text-red-500">*</span></label>
                                <!-- oninput chặn nhập chữ, maxlength khóa cứng 12 ký tự -->
                                <input type="text" name="citizen_id" value="{{ old('citizen_id') }}" minlength="12" maxlength="12" pattern="\d{12}" title="Vui lòng nhập đủ 12 số CCCD" oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 12)" required class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition-colors @error('citizen_id') border-red-500 @enderror">
                                @error('citizen_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Mã số thuế / GPKD <span class="text-red-500">*</span></label>
                                <!-- Thay đổi: oninput dùng regex [^a-zA-Z0-9] để chỉ cho phép chữ và số. Khóa độ dài bằng maxlength="50" -->
                                <input type="text" name="business_license_number" value="{{ old('business_license_number') }}" maxlength="50" oninput="this.value = this.value.replace(/[^a-zA-Z0-9]/g, '')" required class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition-colors @error('business_license_number') border-red-500 @enderror">
                                @error('business_license_number') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Thông tin ngân hàng -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Ngân hàng <span class="text-red-500">*</span></label>
                                <input type="text" name="bank_name" value="{{ old('bank_name') }}" required class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition-colors @error('bank_name') border-red-500 @enderror">
                                @error('bank_name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Số tài khoản <span class="text-red-500">*</span></label>
                                <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition-colors @error('bank_account_number') border-red-500 @enderror">
                                @error('bank_account_number') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Chủ tài khoản <span class="text-red-500">*</span></label>
                                <input type="text" name="bank_account_holder" value="{{ old('bank_account_holder') }}" required class="w-full rounded-lg border-slate-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm transition-colors @error('bank_account_holder') border-red-500 @enderror">
                                @error('bank_account_holder') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Cập nhật File Bắt buộc (CCCD) -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Ảnh CCCD Mặt trước <span class="text-red-500">*</span></label>
                                <input type="file" name="citizen_front_image" accept="image/*" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-300 rounded-lg cursor-pointer @error('citizen_front_image') border-red-500 @enderror">
                                @error('citizen_front_image') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Ảnh CCCD Mặt sau <span class="text-red-500">*</span></label>
                                <input type="file" name="citizen_back_image" accept="image/*" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-300 rounded-lg cursor-pointer @error('citizen_back_image') border-red-500 @enderror">
                                @error('citizen_back_image') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Cập nhật File Pháp lý (Bắt buộc tất cả) -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">File Giấy phép KD <span class="text-red-500">*</span></label>
                                <input type="file" name="business_license_file" accept=".pdf,image/*" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-300 rounded-lg cursor-pointer @error('business_license_file') border-red-500 @enderror">
                                @error('business_license_file') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">File Hợp đồng thuê <span class="text-red-500">*</span></label>
                                <input type="file" name="rental_contract_file" accept=".pdf,image/*" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-300 rounded-lg cursor-pointer @error('rental_contract_file') border-red-500 @enderror">
                                @error('rental_contract_file') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">File Sổ đỏ/Sổ hồng <span class="text-red-500">*</span></label>
                                <input type="file" name="land_certificate_file" accept=".pdf,image/*" required class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 border border-slate-300 rounded-lg cursor-pointer @error('land_certificate_file') border-red-500 @enderror">
                                @error('land_certificate_file') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-200 mt-8">
                        <a href="{{ route('owner.web.venues.index') }}" class="px-5 py-2.5 text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg shadow-sm transition-colors">
                            Hủy bỏ
                        </a>
                        <button type="submit" class="px-5 py-2.5 text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition-colors flex items-center">
                            Nộp hồ sơ & Chờ duyệt
                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>