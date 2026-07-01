@php
    $isEdit = filled($package);

    $oldType = old('type', $package?->type ?? 'week');
    $oldDuration = old('duration', $package?->duration ?? 8);
    $oldDiscount = old('discount_percent', $package?->discount_percent ?? 10);
    $oldMaxSubscribers = old('max_subscribers', $package?->max_subscribers);
    $oldStatus = old('status', $package?->status ?? 'active');
    $oldMaxSessionsPerWeek = old('max_sessions_per_week', data_get($package, 'max_sessions_per_week', 7));

    $oldMaxSessionsPerWeek = max(1, min(7, (int) $oldMaxSessionsPerWeek));
@endphp

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | SportHub</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="min-h-screen bg-slate-50 font-[Inter] text-slate-800">
    <main class="mx-auto max-w-5xl px-6 py-10">
        <a href="{{ route('owner.web.packages.index') }}"
           class="mb-6 inline-flex text-sm font-bold text-emerald-700 hover:text-emerald-800">
            ← Quay lại quản lý gói
        </a>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="mb-6">
                        <p class="text-sm font-bold uppercase tracking-wide text-emerald-600">
                            {{ $isEdit ? 'Cập nhật gói' : 'Tạo gói mới' }}
                        </p>

                        <h1 class="mt-1 text-2xl font-extrabold text-slate-900">
                            {{ $title }}
                        </h1>

                        <p class="mt-1 text-sm text-slate-500">
                            Cơ sở: <span class="font-bold text-slate-700">{{ $venue->name }}</span>
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-semibold text-rose-700">
                            Vui lòng kiểm tra lại thông tin gói.
                        </div>
                    @endif

                    <form method="POST" action="{{ $action }}" class="space-y-6">
                        @csrf

                        @if($method !== 'POST')
                            @method($method)
                        @endif

                        <section class="rounded-2xl border border-slate-200 p-5">
                            <div class="mb-4 flex items-start gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-extrabold text-emerald-700">
                                    1
                                </div>

                                <div>
                                    <h2 class="font-extrabold text-slate-900">
                                        Thông tin gói
                                    </h2>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Tên gói nên ngắn gọn, dễ hiểu với khách.
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-bold text-slate-700">
                                    Tên gói
                                </label>

                                <input name="name"
                                       value="{{ old('name', $package?->name) }}"
                                       placeholder="Ví dụ: Gói 8 tuần, Gói tháng linh hoạt, Gói tập luyện cố định..."
                                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-emerald-500"
                                       required>

                                @error('name')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </section>

                        <section class="rounded-2xl border border-slate-200 p-5">
                            <div class="mb-4 flex items-start gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-extrabold text-emerald-700">
                                    2
                                </div>

                                <div>
                                    <h2 class="font-extrabold text-slate-900">
                                        Loại gói và thời lượng
                                    </h2>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Gói tuần tính theo số tuần. Gói tháng tính theo tháng thực tế, nên tháng 30 ngày có thể sinh 30 buổi nếu khách chọn chơi mỗi ngày.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <label class="package-type-card cursor-pointer rounded-2xl border p-4 hover:border-emerald-400 hover:bg-emerald-50">
                                    <div class="flex items-start gap-3">
                                        <input type="radio"
                                               name="type"
                                               value="week"
                                               class="mt-1 h-4 w-4 text-emerald-600 focus:ring-emerald-500"
                                               @checked($oldType === 'week')>

                                        <div>
                                            <p class="font-extrabold text-slate-900">
                                                Theo tuần
                                            </p>

                                            <p class="mt-1 text-sm text-slate-500">
                                                Phù hợp gói 4 tuần, 8 tuần, 12 tuần.
                                            </p>
                                        </div>
                                    </div>
                                </label>

                                <label class="package-type-card cursor-pointer rounded-2xl border p-4 hover:border-emerald-400 hover:bg-emerald-50">
                                    <div class="flex items-start gap-3">
                                        <input type="radio"
                                               name="type"
                                               value="month"
                                               class="mt-1 h-4 w-4 text-emerald-600 focus:ring-emerald-500"
                                               @checked($oldType === 'month')>

                                        <div>
                                            <p class="font-extrabold text-slate-900">
                                                Theo tháng
                                            </p>

                                            <p class="mt-1 text-sm text-slate-500">
                                                Phù hợp gói 1 tháng, 3 tháng, 6 tháng.
                                            </p>
                                        </div>
                                    </div>
                                </label>
                            </div>

                            @error('type')
                                <p class="mt-2 text-xs text-rose-600">{{ $message }}</p>
                            @enderror

                            <div class="mt-4">
                                <label class="mb-1 block text-sm font-bold text-slate-700">
                                    Thời lượng
                                </label>

                                <input type="number"
                                       min="1"
                                       max="52"
                                       name="duration"
                                       id="duration"
                                       value="{{ $oldDuration }}"
                                       class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-emerald-500"
                                       required>

                                <p id="duration-help" class="mt-1 text-xs text-slate-500">
                                    Theo tuần = số tuần, theo tháng = số tháng.
                                </p>

                                @error('duration')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </section>

                        <section class="rounded-2xl border border-slate-200 p-5">
                            <div class="mb-4 flex items-start gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-extrabold text-emerald-700">
                                    3
                                </div>

                                <div>
                                    <h2 class="font-extrabold text-slate-900">
                                        Cấu hình sử dụng gói
                                    </h2>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Quy định khách được chọn tối đa bao nhiêu buổi mỗi tuần khi mua gói.
                                    </p>
                                </div>
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold text-slate-700">
                                    Số buổi/tuần tối đa khách được chọn
                                </label>

                                <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-4">
                                    @for($i = 1; $i <= 7; $i++)
                                        <label class="weekly-limit-card flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200 p-4 hover:border-emerald-400 hover:bg-emerald-50">
                                            <input type="radio"
                                                   name="max_sessions_per_week"
                                                   value="{{ $i }}"
                                                   class="h-4 w-4 text-emerald-600 focus:ring-emerald-500"
                                                   @checked((int) $oldMaxSessionsPerWeek === $i)>

                                            <span class="text-sm font-extrabold text-slate-800">
                                                {{ $i }} buổi/tuần

                                                @if($i === 7)
                                                    <span class="block text-xs font-semibold text-emerald-600">
                                                        Chơi mỗi ngày
                                                    </span>
                                                @endif
                                            </span>
                                        </label>
                                    @endfor
                                </div>

                                <p class="mt-2 text-xs text-slate-500">
                                    Nếu chọn 7 buổi/tuần, khách có thể chọn lịch chơi mỗi ngày. Với gói tháng, hệ thống sẽ sinh theo số ngày thực tế của tháng.
                                </p>

                                @error('max_sessions_per_week')
                                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </section>

                        <section class="rounded-2xl border border-slate-200 p-5">
                            <div class="mb-4 flex items-start gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-extrabold text-emerald-700">
                                    4
                                </div>

                                <div>
                                    <h2 class="font-extrabold text-slate-900">
                                        Giá trị ưu đãi và giới hạn đăng ký
                                    </h2>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Chủ sân chỉ nhập phần trăm giảm. Giá gốc lấy theo khung giờ khách chọn.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-3">
                                <div>
                                    <label class="mb-1 block text-sm font-bold text-slate-700">
                                        Giảm giá (%)
                                    </label>

                                    <input type="number"
                                           min="0"
                                           max="100"
                                           step="0.01"
                                           name="discount_percent"
                                           id="discount_percent"
                                           value="{{ $oldDiscount }}"
                                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-emerald-500"
                                           required>

                                    @error('discount_percent')
                                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-bold text-slate-700">
                                        Số lượng khách tối đa
                                    </label>

                                    <input type="number"
                                           min="1"
                                           name="max_subscribers"
                                           value="{{ $oldMaxSubscribers }}"
                                           placeholder="Không giới hạn"
                                           class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-emerald-500">

                                    <p class="mt-1 text-xs text-slate-500">
                                        Bỏ trống nếu không giới hạn.
                                    </p>

                                    @error('max_subscribers')
                                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-1 block text-sm font-bold text-slate-700">
                                        Trạng thái
                                    </label>

                                    <select name="status"
                                            class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm outline-none focus:border-emerald-500">
                                        <option value="active" @selected($oldStatus === 'active')>
                                            Bật
                                        </option>

                                        <option value="inactive" @selected($oldStatus === 'inactive')>
                                            Tắt
                                        </option>
                                    </select>

                                    @error('status')
                                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>

                        <section class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <div class="mb-4 flex items-start gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-sm font-extrabold text-emerald-700">
                                    5
                                </div>

                                <div>
                                    <h2 class="font-extrabold text-slate-900">
                                        Chính sách mặc định của gói
                                    </h2>

                                    <p class="mt-1 text-sm text-slate-500">
                                        Những chính sách này nên hiển thị cho khách trước khi mua gói.
                                    </p>
                                </div>
                            </div>

                            <div class="grid gap-3 text-sm font-semibold text-slate-700 md:grid-cols-2">
                                <p>✓ Không thể đổi sân trong gói sau khi kích hoạt</p>
                                <p>✓ Được đổi lịch 1 buổi nếu còn slot trống</p>
                                <p>✓ Có thể tạm dừng gói theo quy định cơ sở</p>
                                <p>✓ Không hoàn tiền sau khi gói đã kích hoạt</p>
                                <p>✓ Có thể gia hạn sau khi hết gói</p>
                                <p>✓ Lịch booking chỉ sinh sau khi thanh toán thành công</p>
                            </div>
                        </section>

                        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                            <a href="{{ route('owner.web.packages.index') }}"
                               class="rounded-xl border border-slate-300 px-5 py-3 text-center text-sm font-extrabold text-slate-700 hover:bg-slate-50">
                                Hủy
                            </a>

                            <button type="submit"
                                    class="rounded-xl bg-emerald-600 px-6 py-3 text-sm font-extrabold text-white hover:bg-emerald-700">
                                {{ $isEdit ? 'Cập nhật gói' : 'Tạo gói' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <aside class="lg:col-span-1">
                <div class="sticky top-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-lg font-extrabold text-slate-900">
                        Xem trước gói
                    </p>

                    <div class="mt-4 rounded-2xl border border-emerald-100 bg-emerald-50 p-5">
                        <p id="preview-name" class="text-xl font-extrabold text-slate-900">
                            Gói đặt sân
                        </p>

                        <p id="preview-duration" class="mt-1 text-sm font-bold text-emerald-700">
                            —
                        </p>

                        <div class="mt-4 space-y-2 text-sm font-semibold text-slate-700">
                            <p id="preview-weekly">✓ Tối đa 7 buổi/tuần</p>
                            <p id="preview-discount">✓ Giảm 10%</p>
                            <p>✓ Giá tính theo sân và khung giờ khách chọn</p>
                            <p>✓ Thanh toán xong mới sinh lịch</p>
                        </div>

                        <div class="mt-4 rounded-xl bg-white px-3 py-2 text-xs font-semibold text-slate-500">
                            Card này mô phỏng cách khách sẽ thấy gói ở trang đặt sân.
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl bg-slate-50 p-4 text-xs font-semibold leading-6 text-slate-600">
                        Nghiệp vụ chuẩn:
                        <br>
                        Khách đăng ký gói
                        →
                        tạo PackageBooking trạng thái pending_payment
                        →
                        thanh toán thành công
                        →
                        sinh toàn bộ Booking
                        →
                        gói chuyển active.
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const nameInput = document.querySelector('input[name="name"]');
            const durationInput = document.querySelector('input[name="duration"]');
            const discountInput = document.querySelector('input[name="discount_percent"]');
            const durationHelp = document.getElementById('duration-help');

            const previewName = document.getElementById('preview-name');
            const previewDuration = document.getElementById('preview-duration');
            const previewWeekly = document.getElementById('preview-weekly');
            const previewDiscount = document.getElementById('preview-discount');

            function getSelectedType() {
                return document.querySelector('input[name="type"]:checked')?.value || 'week';
            }

            function getSelectedWeeklyLimit() {
                return document.querySelector('input[name="max_sessions_per_week"]:checked')?.value || 7;
            }

            function updateActiveCards() {
                document.querySelectorAll('.package-type-card').forEach(card => {
                    const checked = card.querySelector('input[type="radio"]')?.checked;

                    card.classList.toggle('border-emerald-500', checked);
                    card.classList.toggle('bg-emerald-50', checked);
                    card.classList.toggle('ring-2', checked);
                    card.classList.toggle('ring-emerald-100', checked);
                    card.classList.toggle('border-slate-200', !checked);
                });

                document.querySelectorAll('.weekly-limit-card').forEach(card => {
                    const checked = card.querySelector('input[type="radio"]')?.checked;

                    card.classList.toggle('border-emerald-500', checked);
                    card.classList.toggle('bg-emerald-50', checked);
                    card.classList.toggle('ring-2', checked);
                    card.classList.toggle('ring-emerald-100', checked);
                    card.classList.toggle('border-slate-200', !checked);
                });
            }

            function updateDurationHelp() {
                const type = getSelectedType();

                if (type === 'week') {
                    durationInput.max = 52;
                    durationHelp.textContent = 'Nhập số tuần của gói. Ví dụ: 8 nghĩa là gói kéo dài 8 tuần.';
                    return;
                }

                durationInput.max = 24;
                durationHelp.textContent = 'Nhập số tháng của gói. Ví dụ: 1 nghĩa là gói kéo dài 1 tháng thực tế.';
            }

            function updatePreview() {
                const type = getSelectedType();
                const duration = Number(durationInput.value || 0);
                const discount = Number(discountInput.value || 0);
                const weeklyLimit = Number(getSelectedWeeklyLimit());

                const durationText = type === 'week'
                    ? `${duration} tuần`
                    : `${duration} tháng`;

                previewName.textContent = nameInput.value.trim() || 'Gói đặt sân';
                previewDuration.textContent = `Thời hạn ${durationText}`;

                previewWeekly.textContent = weeklyLimit === 7
                    ? '✓ Tối đa 7 buổi/tuần - hỗ trợ chơi mỗi ngày'
                    : `✓ Tối đa ${weeklyLimit} buổi/tuần`;

                previewDiscount.textContent = `✓ Giảm ${discount}%`;

                updateActiveCards();
                updateDurationHelp();
            }

            document.querySelectorAll('input[name="type"], input[name="max_sessions_per_week"]').forEach(input => {
                input.addEventListener('change', updatePreview);
            });

            [nameInput, durationInput, discountInput].forEach(input => {
                input?.addEventListener('input', updatePreview);
            });

            updatePreview();
        });
    </script>
</body>
</html>