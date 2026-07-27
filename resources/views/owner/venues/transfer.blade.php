<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chuyển nhượng cơ sở - Chủ Sân</title>
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
                <span class="text-slate-800 font-medium">Chuyển nhượng</span>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <a href="{{ route('owner.dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">Tổng quan</a>
            <a href="{{ route('owner.web.calendar.index') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">Lịch đặt sân</a>
            <a href="{{ route('owner.web.packages.index') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-2">Quản lý gói</a>
            @include('owner.partials.notification-bell')
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex-1 p-6 lg:p-10 max-w-3xl mx-auto w-full">
        <div class="mb-6">
            <a href="{{ route('owner.web.venues.index') }}" class="text-sm font-medium text-emerald-600 hover:text-emerald-700 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Quay lại danh sách
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-200 bg-slate-50">
                <h2 class="text-xl font-bold text-slate-800">Yêu cầu chuyển nhượng cơ sở</h2>
                <p class="text-sm text-slate-500 mt-1">Cơ sở: <strong class="text-slate-700">{{ $venue->name }}</strong></p>
            </div>

            <div class="p-6">
                {{-- Hiển thị lỗi từ Form Request --}}
                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div>
                                <h3 class="text-sm font-medium text-red-800">Không thể thực hiện yêu cầu:</h3>
                                <ul class="mt-1 text-sm text-red-700 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('owner.web.venues.transfer.store', $venue->id) }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label for="receiver_email" class="block text-sm font-medium text-slate-700 mb-2">Email chủ sân nhận chuyển nhượng <span class="text-red-500">*</span></label>
                        <input type="email" name="receiver_email" id="receiver_email" value="{{ old('receiver_email') }}" required
                               class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                               placeholder="Nhập email tài khoản Chủ sân mới...">
                        
                        <!-- Khu vực hiển thị kết quả AJAX -->
                        <div id="email_result" class="mt-2 text-sm min-h-[20px]">
                            <span class="text-slate-500">Người nhận phải có tài khoản Chủ sân hợp lệ trên hệ thống SportHub.</span>
                        </div>
                    </div>

                    <div class="bg-amber-50 rounded-lg p-4 mb-6 border border-amber-200">
                        <h4 class="text-sm font-semibold text-amber-800 flex items-center mb-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Lưu ý quan trọng
                        </h4>
                        <ul class="text-xs text-amber-700 list-disc list-inside space-y-1">
                            <li>Bạn không thể hoàn tác sau khi Admin đã phê duyệt.</li>
                            <li>Toàn bộ doanh thu các đơn hàng thanh toán sau thời điểm duyệt sẽ thuộc về chủ mới.</li>
                            <li>Yêu cầu tài khoản của bạn phải không có công nợ.</li>
                        </ul>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('owner.web.venues.index') }}" class="px-4 py-2 text-sm font-medium text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50">Hủy</a>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-emerald-600 border border-transparent rounded-lg hover:bg-emerald-700">
                            Gửi yêu cầu kiểm duyệt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('owner.partials.notification-script')
    <!-- Script xử lý AJAX check Email -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const emailInput = document.getElementById('receiver_email');
            const resultDiv = document.getElementById('email_result');
            let timeout = null;

            emailInput.addEventListener('input', function() {
                clearTimeout(timeout);
                const email = this.value.trim();

                // Nếu xóa rỗng thì đưa về text mặc định
                if (!email) {
                    resultDiv.innerHTML = '<span class="text-slate-500">Người nhận phải có tài khoản Chủ sân hợp lệ trên hệ thống SportHub.</span>';
                    return;
                }

                // Hiển thị trạng thái đang tải
                resultDiv.innerHTML = '<span class="text-slate-500 flex items-center"><svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-emerald-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Đang kiểm tra...</span>';

                // Delay 500ms sau khi người dùng ngừng gõ mới gọi API để đỡ tốn tài nguyên
                timeout = setTimeout(() => {
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
                            resultDiv.innerHTML = `<span class="text-emerald-600 font-medium flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Chủ sân nhận: <strong>${data.name}</strong></span>`;
                        } else {
                            resultDiv.innerHTML = `<span class="text-red-500 font-medium flex items-center"><svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg> ${data.message}</span>`;
                        }
                    })
                    .catch(error => {
                        resultDiv.innerHTML = '<span class="text-red-500">Lỗi kết nối khi kiểm tra email.</span>';
                    });
                }, 500);
            });
        });
    </script>
</body>
</html>