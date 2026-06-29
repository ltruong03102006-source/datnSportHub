@extends('layouts.app')

@section('title', 'Chi tiết đặt lịch | SportHub')

@section('content')
@php
    $slotDate = $booking->slot_date?->format('d/m/Y') ?? '';
    $totalPriceStr = number_format((float) $totalGroupPrice, 0, ',', '.') . ' ₫';
    $statusLabel = $booking->status === 'pending' ? 'Chờ chủ sân xác nhận' : $statusMeta['label'];
    
    // Lấy thông tin môn thể thao và SĐT
    $sportName = $booking->court?->venue?->sport?->name ?? 'Thể thao';
    // Ưu tiên lấy SĐT từ bảng OwnerRegistration, nếu không có mới tìm trong bảng Venue
    $venuePhone = $booking->court?->venue?->ownerRegistration?->phone ?? $booking->court?->venue?->phone ?? 'Chưa cập nhật';
    $userPhone = Auth::user()->phone ?? 'Chưa cập nhật';
@endphp

<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8 pb-24">
    
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('account.bookings.index') }}" class="grid h-10 w-10 place-items-center rounded-full bg-stone-200/50 text-zinc-600 transition hover:bg-stone-200">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
        </a>
        <h1 class="text-xl font-extrabold text-zinc-900">Chi tiết đặt lịch</h1>
    </div>

    <div class="mb-5 overflow-hidden rounded-2xl bg-emerald-800 text-white shadow-md">
        <div class="flex items-center gap-4 p-5 sm:p-6">
            <div class="grid h-16 w-16 shrink-0 place-items-center rounded-full bg-emerald-100 text-2xl font-black text-emerald-800 ring-4 ring-emerald-700/50">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="flex-1 space-y-1.5">
                <p class="text-sm font-medium text-emerald-100/80">KH: <span class="font-bold text-white text-base">{{ Auth::user()->name }}</span></p>
                <p class="text-sm font-medium text-emerald-100/80">Đối tượng: <span class="font-bold text-white">Sân {{ $sportName }}</span></p>
                <p class="text-sm font-medium text-emerald-100/80">Số điện thoại: <span class="font-bold text-white">{{ $userPhone }}</span></p>
            </div>
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
        
        <div class="border-b border-stone-100 bg-stone-50/50 px-5 py-4 flex items-center gap-2">
            <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 15.75h3.75M18 19.5V5.25c0-.414-.336-.75-.75-.75H6.75c-.414 0-.75.336-.75.75v14.25c0 .414.336.75.75.75h10.5c.414 0 .75-.336.75-.75Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 3.75V5.25" /><path stroke-linecap="round" stroke-linejoin="round" d="M9 3.75V5.25" /></svg>
            <h2 class="text-base font-extrabold text-zinc-900">Thông tin</h2>
        </div>

        <div class="p-5 sm:p-6 space-y-5">
            <div class="space-y-3">
                <div class="flex items-start gap-x-4">
                    <p class="w-28 shrink-0 text-sm font-medium text-stone-500">Mã lịch đặt:</p>
                    <p class="text-sm font-black text-zinc-900">#{{ $booking->id }}</p>
                </div>
                <div class="flex items-start gap-x-4">
                    <p class="w-28 shrink-0 text-sm font-medium text-stone-500">Trạng thái:</p>
                    <p class="text-sm font-bold {{ $booking->status === 'pending' ? 'text-amber-600' : 'text-emerald-600' }}">{{ $statusLabel }}</p>
                </div>
            </div>

            <div class="border-t border-stone-100 border-dashed"></div>

            <div class="space-y-3">
                <div class="flex items-start gap-x-4">
                    <p class="w-28 shrink-0 text-sm font-medium text-stone-500">Tên CLB:</p>
                    <p class="text-sm font-bold text-zinc-900 leading-relaxed">{{ $booking->court?->venue?->name ?? 'Chưa cập nhật' }}</p>
                </div>
                <div class="flex items-start gap-x-4">
                    <p class="w-28 shrink-0 text-sm font-medium text-stone-500">Địa chỉ:</p>
                    <p class="text-sm font-semibold text-zinc-700 leading-relaxed">{{ $booking->court?->venue?->address ?? 'Chưa cập nhật' }}</p>
                </div>
                <div class="flex items-center gap-x-4">
                    <p class="w-28 shrink-0 text-sm font-medium text-stone-500">Số điện thoại:</p>
                    <div class="flex items-center justify-between flex-1">
                        <p class="text-sm font-bold text-zinc-900">{{ $venuePhone }}</p>
                       
                    </div>
                </div>
            </div>

            <div class="border-t border-stone-100 border-dashed"></div>

            <div class="space-y-3">
                <div class="flex items-start gap-x-4">
    <p class="w-28 shrink-0 text-sm font-medium text-stone-500">Ngày chơi:</p>
    <div class="flex-1">
        <p class="text-sm font-bold text-zinc-900 mb-1.5">{{ $slotDate }}</p>
        @php
            // 1. Sắp xếp mảng ca theo giờ bắt đầu
            $sortedGroup = collect($bookingGroup)->sortBy('start_time')->values();
            $mergedSlots = [];

            if ($sortedGroup->count() > 0) {
                // 2. Khởi tạo mốc thời gian của ca đầu tiên
                $currentCourt = $sortedGroup[0]->court->name;
                $currentStart = substr((string) $sortedGroup[0]->start_time, 0, 5);
                $currentEnd = substr((string) $sortedGroup[0]->end_time, 0, 5);

                // 3. Duyệt từ ca thứ 2 để gộp
                for ($i = 1; $i < $sortedGroup->count(); $i++) {
                    $nextCourt = $sortedGroup[$i]->court->name;
                    $nextStart = substr((string) $sortedGroup[$i]->start_time, 0, 5);
                    $nextEnd = substr((string) $sortedGroup[$i]->end_time, 0, 5);

                    // ĐIỀU KIỆN GỘP: Cùng tên sân VÀ Giờ kết thúc ca trước == Giờ bắt đầu ca sau
                    if ($currentCourt === $nextCourt && $currentEnd === $nextStart) {
                        $currentEnd = $nextEnd; // Kéo dài thời gian kết thúc
                    } else {
                        // Nếu bị ngắt quãng, lưu lại dải thời gian vừa gộp và làm mới biến
                        $mergedSlots[] = "- Sân $currentCourt: $currentStart - $currentEnd";
                        $currentCourt = $nextCourt;
                        $currentStart = $nextStart;
                        $currentEnd = $nextEnd;
                    }
                }
                // Nhớ lưu lại dải thời gian của ca cuối cùng
                $mergedSlots[] = "- Sân $currentCourt: $currentStart - $currentEnd";
            }
        @endphp

        {{-- 4. In danh sách ca đã được gộp đẹp mắt --}}
        @foreach($mergedSlots as $slotInfo)
            <p class="text-sm font-semibold text-zinc-700">{{ $slotInfo }}</p>
        @endforeach
    </div>
</div>
                <div class="flex items-start gap-x-4">
                    <p class="w-28 shrink-0 text-sm font-medium text-stone-500">Tổng giờ:</p>
                    <p class="text-sm font-bold text-zinc-900">{{ $totalDurationStr }}</p>
                </div>
                
                <div class="flex items-start gap-x-4 mt-2">
                    <p class="w-28 shrink-0 text-sm font-medium text-stone-500">Tổng tiền:</p>
                    <p class="text-base font-black text-emerald-600">{{ $totalPriceStr }}</p>
                </div>

                <div class="flex items-start gap-x-4 pt-2">
                    <p class="w-28 shrink-0 text-sm font-medium text-stone-500">Trạng thái TT:</p>
                    <div class="flex-1">
                        @if(($booking->payment_status ?? 'unpaid') === 'paid')
                            <p class="text-sm font-bold text-zinc-900 mb-1">Đã thanh toán</p>
                            <span class="inline-flex items-center gap-1.5 rounded bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-600 ring-1 ring-inset ring-emerald-500/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                Giao dịch thành công
                            </span>
                        @else
                            <p class="text-sm font-bold text-zinc-900 mb-1">Chưa thanh toán</p>
                            <span class="inline-flex items-center gap-1.5 rounded bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-600 ring-1 ring-inset ring-amber-500/20">
                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                Chờ thanh toán
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            @if(($booking->payment_status ?? 'unpaid') !== 'paid' && $booking->status !== 'cancelled' && $booking->status !== 'rejected')
                <div class="border-t border-stone-100 border-dashed my-5"></div>
                
                <div class="rounded-2xl border border-stone-200 bg-stone-50 p-5">
                    <h3 class="mb-4 text-center text-sm font-extrabold uppercase tracking-wider text-zinc-900">Thanh toán đơn hàng</h3>
                    
                    <div class="flex flex-col md:flex-row gap-6 items-center md:items-start justify-center">
                        <!-- VietQR Section -->
                        <div class="flex flex-col items-center p-4 bg-white rounded-xl shadow-sm border border-stone-100 w-full max-w-xs">
                            <p class="text-xs font-bold text-stone-500 mb-2 uppercase">Quét mã QR (VietQR)</p>
                            @php
                                $owner = $booking->court?->venue?->owner;
                                $legalDoc = $booking->court?->venue?->legalDocument;
                                
                                // Ưu tiên cấu hình bank ở User Profile, sau đó mới đến LegalDocument
                                $bankName = $owner->bank_name ?? $legalDoc?->bank_name;
                                $bankAccountNo = $owner->bank_account_no ?? $legalDoc?->bank_account_number;
                                $bankAccountName = $owner->bank_account_name ?? $legalDoc?->bank_account_holder ?? 'CHU SAN';
                                
                                $hasBankInfo = $bankName && $bankAccountNo;
                                
                                if ($hasBankInfo) {
                                    $bankId = trim($bankName); 
                                    $accountNo = trim($bankAccountNo);
                                    $accountName = trim($bankAccountName);
                                    
                                    // Tạo chuỗi nội dung chuyển khoản không dấu
                                    $userName = strtoupper(\Illuminate\Support\Str::slug(Auth::user()->name, ' '));
                                    $addInfo = 'THANH TOAN SAN ' . $booking->id . ' KH ' . $userName;
                                    
                                    $qrUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-compact2.png?amount={$totalGroupPrice}&addInfo=" . urlencode($addInfo) . "&accountName=" . urlencode($accountName);
                                }
                            @endphp
                            
                            @if($hasBankInfo)
                                <img src="{{ $qrUrl }}" alt="VietQR Payment" class="w-48 h-48 rounded-lg mb-3">
                                <p class="text-center text-xs text-stone-500">Sử dụng App ngân hàng để quét mã.<br>Số tiền: <strong class="text-emerald-600">{{ number_format($totalGroupPrice, 0, ',', '.') }} đ</strong></p>
                            @else
                                <div class="flex h-48 w-48 items-center justify-center rounded-lg border-2 border-dashed border-stone-200 bg-stone-50 mb-3">
                                    <p class="text-center text-xs text-stone-400 px-4">Chủ sân chưa cấu hình tài khoản ngân hàng</p>
                                </div>
                                <p class="text-center text-xs text-stone-500">Vui lòng sử dụng VNPay hoặc thanh toán tại sân.</p>
                            @endif
                        </div>

                        <!-- VNPay Section -->
                        <div class="flex flex-col justify-center items-center h-full w-full max-w-xs">
                            <p class="text-sm font-medium text-stone-500 mb-4">Hoặc thanh toán qua cổng</p>
                            <a href="{{ route('vnpay.payment', $booking->id) }}" class="flex items-center justify-center gap-2 w-full rounded-xl bg-blue-600 px-4 py-3.5 text-sm font-black text-white shadow-md shadow-blue-600/20 transition hover:bg-blue-700 active:scale-[0.98]">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M4 10V14C4 18.4183 7.58172 22 12 22C16.4183 22 20 18.4183 20 14V10C20 5.58172 16.4183 2 12 2C7.58172 2 4 5.58172 4 10Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M12 6V11L15 14" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Thanh toán VNPay
                            </a>
                            <p class="text-center text-xs text-stone-400 mt-3">Hỗ trợ quét mã VNPay, thẻ ATM nội địa, thẻ quốc tế.</p>
                        </div>
                    </div>
                </div>
            @endif

            @if($booking->status === 'cancelled')
                @php
                    // FIX: Tính tổng phí phạt và hoàn lại của TẤT CẢ các ca trong đơn
                    $totalCancelFee = $bookingGroup->sum('cancellation_fee');
                    $totalRefund = $bookingGroup->sum('refund_amount');
                @endphp
                <div class="border-t border-stone-100 border-dashed"></div>
                
                <div class="rounded-xl border border-rose-200 bg-rose-50/50 p-4 sm:p-5">
                    <h4 class="mb-4 text-sm font-extrabold uppercase tracking-wider text-rose-700">Thông tin Hủy & Hoàn tiền</h4>
                    
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-4">
                            <span class="text-sm font-medium text-stone-500">Lý do hủy:</span>
                            <span class="text-sm font-bold text-rose-600 text-right">{{ $booking->cancel_reason ?? 'Không có lý do' }}</span>
                        </div>

                        <div class="flex items-start justify-between gap-4">
                            <span class="text-sm font-medium text-stone-500">Phí phạt hủy:</span>
                            <span class="text-sm font-semibold text-zinc-700">{{ number_format($totalCancelFee, 0, ',', '.') }} ₫</span>
                        </div>

                        <div class="mt-3 flex items-start justify-between gap-4 border-t border-rose-100 pt-3">
                            <span class="text-sm font-bold text-zinc-900">Số tiền hoàn lại:</span>
                            <span class="text-lg font-black text-emerald-600">{{ number_format($totalRefund, 0, ',', '.') }} ₫</span>
                        </div>
                        
                        {{-- <div class="mt-1 text-right">
                            <span class="text-xs font-medium italic text-amber-600">
                                * Trạng thái: {{ ($booking->refund_status ?? '') === 'completed' ? 'Đã hoàn tất' : 'Đang xử lý (1-3 ngày làm việc)' }}
                            </span>
                        </div> --}}
                    </div>
                </div>
            @endif

            </div>
    </div>

    <div class="mt-6 flex flex-col sm:flex-row gap-3">
        <a href="{{ route('account.bookings.index') }}" class="flex-1 flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-3.5 text-sm font-black uppercase tracking-widest text-white shadow-md shadow-emerald-600/20 transition hover:bg-emerald-700 active:scale-[0.98]">
            Quản lý lịch đặt
        </a>
    </div>

</div>
@endsection