<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hợp đồng Chuyển nhượng Cơ sở Thể thao HDCN-#{{ $transfer->id }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;1,400&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; padding: 0 !important; }
            .contract-card { border: none !important; shadow: none !important; margin: 0 !important; padding: 0 !important; }
        }
        .font-serif-legal { font-family: 'Merriweather', Georgia, serif; }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 antialiased min-h-screen py-8 px-4">

    <!-- Thanh công cụ điều hướng / In ấn (no-print) -->
    <div class="max-w-4xl mx-auto mb-6 flex justify-between items-center no-print">
        <a href="{{ (request()->routeIs('admin.venue-transfers.contract') || (auth()->check() && auth()->user()->role === 'admin')) ? route('admin.venue-transfers.show', $transfer->id) : route('owner.web.venues.transfers.history') }}" 
           class="inline-flex items-center text-sm font-semibold text-slate-700 hover:text-emerald-600 bg-white px-4 py-2 rounded-xl shadow-sm border border-slate-200 transition-colors">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            {{ (request()->routeIs('admin.venue-transfers.contract') || (auth()->check() && auth()->user()->role === 'admin')) ? 'Quay lại Yêu cầu chuyển nhượng #' . $transfer->id : 'Quay lại Danh sách hợp đồng' }}
        </a>

        <div class="flex items-center gap-3">
            {{-- Nút GỬI HỢP ĐỒNG: Dành cho Bên A khi hợp đồng ở dạng nháp --}}
            @if($transfer->from_owner_id === auth()->id() && in_array($transfer->status, ['draft', 'pending']))
                <form action="{{ route('owner.web.venues.transfers.send', $transfer->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                        Gửi hợp đồng cho Bên B
                    </button>
                </form>
            @endif

            {{-- Nút ĐIỀN HỒ SƠ: Dành cho Bên B khi nhận được hợp đồng --}}
            @if($transfer->to_owner_id === auth()->id() && in_array($transfer->status, ['sent', 'pending']))
                <a href="{{ route('owner.web.venues.transfers.accept', $transfer->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
                    Điền hồ sơ nhận sân
                </a>
            @endif

            {{-- Nút KÝ HỢP ĐỒNG: Chỉ xuất hiện cho Bên B khi ĐÃ ĐIỀN HỒ SƠ (filled) --}}
            @if($transfer->to_owner_id === auth()->id() && $transfer->status === 'filled' && !$transfer->receiver_signed_at)
                <form action="{{ route('owner.web.venues.transfers.sign', $transfer->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-bold rounded-xl shadow-sm transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                        Ký hợp đồng
                    </button>
                </form>
            @elseif($transfer->receiver_signed_at)
                <span class="inline-flex items-center px-3 py-1.5 bg-emerald-50 text-emerald-700 text-xs font-bold rounded-xl border border-emerald-200 shadow-sm">
                    <svg class="w-4 h-4 mr-1.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Đã ký điện tử
                </span>
            @endif

            {{-- Nút HỦY CHUYỂN NHƯỢNG: Dành cho cả 2 bên khi chưa approved/rejected --}}
            @if(in_array($transfer->status, ['draft', 'sent', 'pending', 'filled', 'signed', 'pending_admin']) && ($transfer->from_owner_id === auth()->id() || $transfer->to_owner_id === auth()->id()))
                <form action="{{ route('owner.web.venues.transfers.cancel', $transfer->id) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn hủy hợp đồng chuyển nhượng này? Cơ sở sẽ không còn trong quá trình chuyển nhượng.');">
                    @csrf
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-red-50 hover:bg-red-600 hover:text-white text-red-700 text-sm font-bold rounded-xl border border-red-200 shadow-sm transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        Hủy chuyển nhượng
                    </button>
                </form>
            @endif

            <button onclick="window.print()" 
                    class="inline-flex items-center px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-bold rounded-xl shadow-sm transition">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                In / Tải PDF
            </button>
        </div>
    </div>

    <!-- BANNER LÝ DO TỪ CHỐI CỦA ADMIN -->
    @if($transfer->status === 'rejected')
        <div class="max-w-4xl mx-auto mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-800 shadow-sm flex items-start gap-3">
            <svg class="w-5 h-5 text-red-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <div>
                <strong class="font-bold text-red-900 block mb-1">Bị Admin từ chối:</strong>
                <p class="whitespace-pre-line text-red-800">{{ $transfer->admin_note ?? 'Admin đã từ chối yêu cầu chuyển nhượng này.' }}</p>
            </div>
        </div>
    @endif

    <!-- TỜ VĂN BẢN HỢP ĐỒNG (A4 STYLE) -->
    <div class="max-w-4xl mx-auto bg-white border border-slate-300 rounded-xl shadow-lg p-8 md:p-14 contract-card font-serif-legal leading-relaxed text-slate-900">
        
        <!-- QUỐC HIỆU tiêu ngữ -->
        <div class="text-center mb-8">
            <h3 class="font-bold text-base md:text-lg uppercase tracking-wider text-slate-900">CỘNG HOÀ XÃ HỘI CHỦ NGHĨA VIỆT NAM</h3>
            <h4 class="font-semibold text-sm md:text-base mt-1 text-slate-800">Độc lập – Tự do – Hạnh phúc</h4>
            <div class="text-slate-600 text-sm mt-1">-----o0o-----</div>

            <div class="text-right italic text-sm mt-4 text-slate-700">
                {{ $transfer->contract_location ?? 'Thành phố Hồ Chí Minh' }}, ngày {{ \Carbon\Carbon::parse($transfer->contract_date ?? $transfer->created_at)->day }} tháng {{ \Carbon\Carbon::parse($transfer->contract_date ?? $transfer->created_at)->month }} năm {{ \Carbon\Carbon::parse($transfer->contract_date ?? $transfer->created_at)->year }}
            </div>
        </div>

        <!-- TIÊU ĐỀ HỢP ĐỒNG -->
        <div class="text-center mb-10">
            <h1 class="text-xl md:text-2xl font-bold uppercase tracking-wide text-slate-950">
                HỢP ĐỒNG CHUYỂN NHƯỢNG CƠ SỞ THỂ THAO
            </h1>
            <p class="text-sm font-sans font-semibold text-slate-600 mt-1">
                (Số: HDCN-#{{ $transfer->id }})
            </p>
        </div>

        <!-- CĂN CỨ PHÁP LÝ -->
        <div class="space-y-2 text-sm italic mb-8 text-slate-800">
            <p>– Căn cứ Bộ luật dân sự 2015 của nước Cộng hòa xã hội chủ nghĩa Việt Nam;</p>
            <p>– Căn cứ Luật thương mại số 36/2005/QH11 của Nước cộng hòa xã hội chủ nghĩa Việt Nam được Quốc hội khóa 11 thông qua ngày 14/6/2005;</p>
            <p>– Căn cứ nhu cầu và thỏa thuận tự nguyện của các bên Hôm nay, ngày {{ \Carbon\Carbon::parse($transfer->contract_date ?? $transfer->created_at)->day }} tháng {{ \Carbon\Carbon::parse($transfer->contract_date ?? $transfer->created_at)->month }} năm {{ \Carbon\Carbon::parse($transfer->contract_date ?? $transfer->created_at)->year }} tại {{ $transfer->contract_location ?? 'Thành phố Hồ Chí Minh' }}.</p>
        </div>

        <p class="font-bold text-sm mb-4">Chúng tôi gồm:</p>

        <!-- BÊN A -->
        <div class="mb-6 space-y-2 text-sm">
            <h4 class="font-bold uppercase text-slate-900 border-b border-slate-200 pb-1">BÊN CHUYỂN NHƯỢNG (BÊN A):</h4>
            <p>Ông/Bà: <strong>{{ $transfer->sender_data['owner_name'] ?? $transfer->fromOwner->name ?? $transfer->fromOwner->full_name ?? 'Bên chuyển nhượng' }}</strong></p>
            <p>Ngày sinh: <strong>{{ !empty($transfer->sender_data['dob']) ? \Carbon\Carbon::parse($transfer->sender_data['dob'])->format('d/m/Y') : '......................................' }}</strong></p>
            <p>Chỗ ở hiện tại: <strong>{{ $transfer->sender_data['address'] ?? '......................................' }}</strong></p>
            <p>Email liên hệ: <strong>{{ $transfer->fromOwner->email }}</strong></p>
            <p>Số Căn cước công dân / CMND: <strong>{{ $transfer->venue->legalDocument->citizen_id ?? '......................................' }}</strong></p>
            <p>Là chủ sở hữu hợp pháp của Cơ sở thể thao: <strong>{{ $transfer->venue->name ?? 'N/A' }}</strong></p>
            <p>Địa chỉ cơ sở: <strong>{{ $transfer->venue->address ?? 'Chưa cập nhật' }}</strong></p>
        </div>

        <!-- BÊN B -->
        <div class="mb-8 space-y-2 text-sm">
            <h4 class="font-bold uppercase text-slate-900 border-b border-slate-200 pb-1">BÊN ĐƯỢC CHUYỂN NHƯỢNG (BÊN B):</h4>
            <p>Ông/Bà: <strong>{{ $transfer->receiver_data['owner_name'] ?? $transfer->toOwner->name ?? $transfer->toOwner->full_name ?? 'Bên nhận chuyển nhượng' }}</strong></p>
            <p>Ngày sinh: <strong>{{ !empty($transfer->receiver_data['dob']) ? \Carbon\Carbon::parse($transfer->receiver_data['dob'])->format('d/m/Y') : '......................................' }}</strong></p>
            <p>Số Căn cước công dân / CMND: <strong>{{ $transfer->receiver_data['citizen_id'] ?? '......................................' }}</strong></p>
            <p>Chỗ ở hiện tại: <strong>{{ $transfer->receiver_data['address'] ?? '......................................' }}</strong></p>
            <p>Email đăng ký: <strong>{{ $transfer->toOwner->email }}</strong></p>
        </div>

        <p class="text-sm mb-6 font-medium italic">
            Hai bên cùng thỏa thuận sang nhượng Cơ sở thể thao <strong>{{ $transfer->venue->name ?? '' }}</strong> với nội dung chi tiết như sau:
        </p>

        <!-- ĐIỀU 1 -->
        <div class="mb-6 space-y-2 text-sm">
            <h4 class="font-bold text-slate-900">ĐIỀU 1: ĐỐI TƯỢNG CHUYỂN NHƯỢNG</h4>
            <p class="text-justify">
                Bên A đồng ý chuyển nhượng lại toàn bộ cơ sở thể thao <strong>{{ $transfer->venue->name ?? '' }}</strong> cho bên B cùng tất cả các cơ sở vật chất, trang thiết bị bên trong (được chi tiết bằng Biên bản giao nhận kèm theo Hợp đồng này).
            </p>
        </div>

        <!-- ĐIỀU 2 -->
        <div class="mb-6 space-y-2 text-sm">
            <h4 class="font-bold text-slate-900">ĐIỀU 2: BÀN GIAO VÀ QUYỀN SỞ HỮU CƠ SỞ</h4>
            <p class="text-justify">
                1. Bên A có trách nhiệm bàn giao toàn bộ cơ sở thể thao <strong>{{ $transfer->venue->name ?? '' }}</strong>, trang thiết bị đi kèm và các giấy tờ pháp lý liên quan cho Bên B theo đúng thỏa thuận của hai bên.
            </p>
            <p class="text-justify">
                2. Kể từ thời điểm hợp đồng này được ký kết điện tử và được Admin phê duyệt trên hệ thống SportHub, quyền sở hữu, vận hành và khai thác cơ sở thể thao <strong>{{ $transfer->venue->name ?? '' }}</strong> chính thức thuộc về Bên B.
            </p>
        </div>

        <!-- ĐIỀU 3 -->
        <div class="mb-6 space-y-2 text-sm">
            <h4 class="font-bold text-slate-900">ĐIỀU 3: CAM KẾT VÀ TRÁCH NHIỆM CỦA CÁC BÊN TRONG THỜI GIAN CHUYỂN NHƯỢNG</h4>
            <p class="font-bold text-slate-800">1. Trách nhiệm của Bên A:</p>
            <ul class="list-disc list-inside space-y-1 text-justify pl-2">
                <li>Cơ sở thể thao <strong>{{ $transfer->venue->name ?? '' }}</strong> được chuyển nhượng này đang và chỉ thuộc quyền sở hữu hợp pháp của Bên A, không có bất kỳ tranh chấp nào và không thuộc bất kỳ thỏa thuận với bên thứ ba nào khác.</li>
                <li>Bảo đảm quyền sử dụng trọn vẹn hợp pháp và tạo mọi điều kiện thuận lợi, hỗ trợ để Bên B vận hành, kinh doanh đạt hiệu quả.</li>
                <li>Hỗ trợ, bảo đảm cho Bên B nguồn nhân lực, lao động hiện có tại điểm sân tối thiểu 01 tháng sau khi chuyển nhượng.</li>
                <li>Đã hoàn thành đầy đủ các nghĩa vụ tài chính của cơ sở đối với các bên thứ ba từ trước cho đến thời điểm ký hợp đồng này.</li>
                <li>Yêu cầu Bên B tiếp nhận bàn giao và vận hành cơ sở theo đúng thỏa thuận.</li>
                <li>Bàn giao toàn bộ trang thiết bị đồ dùng hiện có ngay sau khi ký kết hợp đồng, đảm bảo trung thực không giấu diếm.</li>
            </ul>

            <p class="font-bold text-slate-800 mt-3">2. Trách nhiệm của Bên B:</p>
            <ul class="list-disc list-inside space-y-1 text-justify pl-2">
                <li>Nhận chuyển nhượng sang cơ sở thể thao <strong>{{ $transfer->venue->name ?? '' }}</strong> và trang thiết bị theo đúng thỏa thuận.</li>
                <li>Tiếp nhận bàn giao cơ sở và tuân thủ các quy định vận hành trên nền tảng.</li>
                <li>Chịu trách nhiệm về toàn bộ hoạt động kinh doanh và pháp lý của mình từ thời điểm nhận bàn giao.</li>
                <li>Yêu cầu Bên A hỗ trợ đối soát dữ liệu và bàn giao đầy đủ chứng từ cần thiết.</li>
            </ul>
        </div>

        <!-- ĐIỀU 4 -->
        <div class="mb-6 space-y-2 text-sm">
            <h4 class="font-bold text-slate-900">ĐIỀU 4: VI PHẠM HỢP ĐỒNG VÀ GIẢI QUYẾT TRANH CHẤP HỢP ĐỒNG</h4>
            <p class="text-justify">
                1. Vì bất cứ lý do gì mà một trong hai bên không thực hiện hay thực hiện không đúng những thỏa thuận đã thống nhất tại Hợp đồng này sẽ bị coi là vi phạm Hợp đồng và phải chịu trách nhiệm bồi thường thiệt hại cho bên còn lại.
            </p>
            <p class="text-justify">
                2. Khi có tranh chấp các bên ưu tiên giải quyết thông qua thương lượng, hòa giải. Trong trường hợp không thể thỏa thuận, một trong hai bên có quyền đưa sự việc ra Tòa án có thẩm quyền để giải quyết.
            </p>
        </div>

        <!-- ĐIỀU 5 -->
        <div class="mb-6 space-y-2 text-sm">
            <h4 class="font-bold text-slate-900">ĐIỀU 5: CHẤM DỨT HỢP ĐỒNG</h4>
            <p class="text-justify">
                Hợp đồng sẽ chấm dứt khi:
            </p>
            <ul class="list-disc list-inside space-y-1 pl-2">
                <li>Sau khi 02 (hai) bên đã hoàn thành toàn bộ các nghĩa vụ và thỏa thuận trong Hợp đồng;</li>
                <li>Hai bên thống nhất thỏa thuận chấm dứt hợp đồng trước thời hạn bằng văn bản;</li>
                <li>Đơn phương chấm dứt hợp đồng theo quy định của pháp luật.</li>
            </ul>
        </div>

        <!-- ĐIỀU 6 -->
        <div class="mb-10 space-y-2 text-sm">
            <h4 class="font-bold text-slate-900">ĐIỀU 6: HIỆU LỰC HỢP ĐỒNG</h4>
            <p class="text-justify">
                Hợp đồng này có hiệu lực kể từ ngày hai bên xác nhận thành công trên hệ thống SportHub. Hợp đồng được lập thành 02 bản điện tử/văn bản có giá trị pháp lý như nhau, mỗi bên giữ 01 bản để thực hiện.
            </p>
        </div>

        <!-- CHỮ KÝ XÁC NHẬN HAI BÊN -->
        <div class="pt-8 border-t border-slate-300 grid grid-cols-2 gap-8 text-center text-sm font-sans">
            <!-- ĐẠI DIỆN BÊN A -->
            <div class="flex flex-col items-center">
                <h5 class="font-bold uppercase text-slate-900">ĐẠI DIỆN BÊN A</h5>
                <p class="text-xs text-slate-500 italic mb-4">(Bên Chuyển nhượng)</p>
                <div class="font-bold text-slate-900 text-base mb-3">{{ $transfer->sender_data['owner_name'] ?? $transfer->fromOwner->name ?? $transfer->fromOwner->full_name }}</div>
                
                @if($transfer->sender_signed_at)
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-left text-xs space-y-1 w-full max-w-xs shadow-sm">
                        <div class="font-bold text-emerald-700 flex items-center gap-1 border-b border-emerald-200 pb-1 mb-1">
                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>✓ Chữ ký số Điện tử xác thực</span>
                        </div>
                        <div class="text-slate-700"><strong>Thời gian ký:</strong> {{ \Carbon\Carbon::parse($transfer->sender_signed_at)->format('H:i:s - d/m/Y') }}</div>
                        <div class="text-slate-700 truncate"><strong>Tài khoản:</strong> {{ $transfer->sender_signed_account ?? $transfer->fromOwner->email }}</div>
                        <div class="text-slate-700"><strong>IP xác thực:</strong> {{ $transfer->sender_signed_ip ?? '127.0.0.1' }}</div>
                    </div>
                @else
                    <div class="text-xs text-emerald-600 font-semibold mt-1">✓ Đã xác nhận khởi tạo</div>
                @endif
            </div>

            <!-- ĐẠI DIỆN BÊN B -->
            <div class="flex flex-col items-center">
                <h5 class="font-bold uppercase text-slate-900">ĐẠI DIỆN BÊN B</h5>
                <p class="text-xs text-slate-500 italic mb-4">(Bên Nhận chuyển nhượng)</p>
                <div class="font-bold text-slate-900 text-base mb-3">{{ $transfer->receiver_data['owner_name'] ?? $transfer->toOwner->name ?? $transfer->toOwner->full_name }}</div>
                
                @if($transfer->receiver_signed_at)
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-left text-xs space-y-1 w-full max-w-xs shadow-sm">
                        <div class="font-bold text-emerald-700 flex items-center gap-1 border-b border-emerald-200 pb-1 mb-1">
                            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span>✓ Chữ ký số Điện tử xác thực</span>
                        </div>
                        <div class="text-slate-700"><strong>Thời gian ký:</strong> {{ \Carbon\Carbon::parse($transfer->receiver_signed_at)->format('H:i:s - d/m/Y') }}</div>
                        <div class="text-slate-700 truncate"><strong>Tài khoản:</strong> {{ $transfer->receiver_signed_account ?? $transfer->toOwner->email }}</div>
                        <div class="text-slate-700"><strong>IP xác thực:</strong> {{ $transfer->receiver_signed_ip ?? '127.0.0.1' }}</div>
                    </div>
                @elseif($transfer->status === 'approved')
                    <div class="text-xs text-emerald-600 font-semibold mt-1">✓ Đã duyệt và hoàn tất</div>
                @elseif(in_array($transfer->status, ['signed', 'pending_admin']))
                    <div class="text-xs text-cyan-600 font-semibold mt-1">⏳ Đã ký hợp đồng (Chờ Admin duyệt)</div>
                @elseif($transfer->status === 'filled')
                    <div class="text-xs text-purple-600 font-semibold mt-1">⏳ Đã điền hồ sơ (Chờ Bên B ký)</div>
                @elseif(in_array($transfer->status, ['sent', 'pending']))
                    <div class="text-xs text-blue-600 font-semibold mt-1">⏳ Đã gửi (Chờ Bên B điền hồ sơ)</div>
                @elseif($transfer->status === 'draft')
                    <div class="text-xs text-amber-600 font-semibold mt-1">⏳ Hợp đồng nháp (Chưa gửi)</div>
                @elseif($transfer->status === 'rejected')
                    <div class="text-xs text-red-600 font-bold mt-1">✗ Bị Admin từ chối</div>
                @else
                    <div class="text-xs text-slate-500 font-semibold mt-1">✗ Đã hủy</div>
                @endif
            </div>
        </div>

    </div>

</body>
</html>
