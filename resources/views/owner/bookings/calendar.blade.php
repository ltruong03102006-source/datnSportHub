@extends('owner.layoutOwner.app')

@section('title','Lịch đặt sân - SportHub')

@section('content')
    <main class="container-fluid page-shell py-4">
    <section class="page-hero">
        <div>
            <h1>Lịch trình đặt sân</h1>
            <p>Xem lịch theo ngày, tuần, tháng và xử lý nhanh các booking đang chờ xác nhận.</p>
        </div>
        <div class="hero-actions">
            <button type="button" class="btn-owner-outline" id="go-today">
                Hôm nay
            </button>
            <a href="{{ route('owner.web.venues.index') }}" class="btn-owner">
                Quản lý sân
            </a>
        </div>
    </section>

    <section class="stats-grid" aria-label="Tổng quan lịch đặt">
        <div class="stat-card">
            <div class="label">Lịch hôm nay</div>
            <div class="value" id="today-booking-count">{{ $todayBookings }}</div>
            <div class="hint">Không tính đơn đã hủy/từ chối</div>
        </div>

        <div class="stat-card">
            <div class="label">Đã xác nhận</div>
            <div class="value text-success">{{ $confirmedBookings }}</div>
            <div class="hint">Lịch sắp phục vụ</div>
        </div>
        <div class="stat-card">
            <div class="label">Tuần này</div>
            <div class="value">{{ $weekBookings }}</div>
            <div class="hint">Booking đang hoạt động</div>
        </div>
        <div class="stat-card">
            <div class="label">Sân con</div>
            <div class="value">{{ $totalCourts }}</div>
            <div class="hint">{{ $venues->count() }} cơ sở đang quản lý</div>
        </div>
    </section>

    <section class="filter-panel">
        <div class="filter-grid">
            <div>
                <label for="venue-filter">Điểm sân</label>
                <select id="venue-filter" class="form-select">
                    <option value="">Tất cả điểm sân</option>
                    @foreach ($venues as $venue)
                        <option value="{{ $venue->id }}">{{ $venue->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="court-filter">Sân con</label>
                <select id="court-filter" class="form-select">
                    <option value="">Tất cả sân con</option>
                    @foreach ($venues as $venue)
                        @foreach ($venue->courts as $court)
                            <option value="{{ $court->id }}" data-venue="{{ $venue->id }}">
                                {{ $venue->name }} - {{ $court->name }}
                            </option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status-filter">Trạng thái</label>
                <select id="status-filter" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="confirmed">Đã xác nhận</option>
                    <option value="completed">Đã hoàn thành</option>
                    <option value="cancelled">Đã hủy</option>
                </select>
            </div>
            <div>
                <label for="date-jump">Tới ngày</label>
                <input id="date-jump" type="date" class="form-control">
            </div>
        </div>

        <div class="legend-strip" aria-label="Chú thích trạng thái">
            <span class="legend-item"><i class="legend-dot" style="background:#047857"></i> Đã xác nhận</span>
            <span class="legend-item"><i class="legend-dot" style="background:#2563eb"></i> Đã hoàn thành</span>
            <span class="legend-item"><i class="legend-dot" style="background:#64748b"></i> Đã hủy</span>
        </div>
    </section>

    <section class="schedule-layout">
        <div class="calendar-card">
            <div id="booking-calendar"></div>
        </div>

        <aside class="agenda-card">
            <div class="agenda-header">
                <h2>Lịch trong khung đang xem</h2>
                <p id="agenda-range-label">Các booking sẽ hiện ở đây sau khi tải lịch.</p>
            </div>
            <div id="agenda-list" class="agenda-list">
                <div class="agenda-empty">Đang tải lịch...</div>
            </div>
        </aside>
    </section>
</main>

<div class="modal fade" id="booking-detail-modal" tabindex="-1" aria-labelledby="booking-detail-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="small text-secondary" id="booking-code"></div>
                    <h2 class="modal-title fs-5 fw-bold" id="booking-detail-title">Chi tiết lịch đặt</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div id="booking-action-alert" class="alert d-none" role="alert"></div>
                <dl class="detail-grid">
                    <dt>Khách hàng</dt>
                    <dd>
                        <div id="detail-customer" class="fw-bold"></div>
                        <small class="text-secondary fw-normal d-block" id="detail-email"></small>
                        
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <span id="detail-phone" class="badge bg-light text-dark border border-secondary-subtle fs-6"></span>
                            <a href="#" id="btn-call-customer" class="btn btn-sm btn-success p-1 px-2" title="Gọi điện">
                                <svg class="w-4 h-4" style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                            </a>
                            <a href="#" target="_blank" id="btn-zalo-customer" class="btn btn-sm btn-primary p-1 px-2" title="Chat Zalo">
                                <span style="font-size: 12px; font-weight: bold;">Zalo</span>
                            </a>
                        </div>
                        </dd>
                    <dt>Điểm sân</dt>
                    <dd id="detail-venue"></dd>
                    <dt>Sân con</dt>
                    <dd id="detail-court"></dd>
                    <dt>Thời gian</dt>
                    <dd id="detail-time"></dd>

                    <dt>Trạng thái</dt>
                    <dd><span class="status-pill" id="detail-status"></span></dd>
                    <dt>Tổng tiền</dt>
                    <dd id="detail-price"></dd>
               
                    <!-- BẮT ĐẦU THÊM MỚI -->
                    <dt id="detail-services-label" class="d-none mt-2">Dịch vụ kèm</dt>
                    <dd id="detail-services-list" class="d-none mt-2"></dd>
                    <!-- KẾT THÚC THÊM MỚI -->

                    <dt class="d-none" id="detail-cancel-label">Lý do hủy</dt>
                    <dd class="d-none text-danger" id="detail-cancel-reason"></dd>
                </dl>
            </div>
            <div id="booking-actions" class="modal-footer d-none">
                <button type="button" id="reject-booking" class="btn btn-outline-danger">Từ chối</button>
                <button type="button" id="confirm-booking" class="btn btn-success">Xác nhận</button>
            </div>
            <div id="booking-cancel" class="modal-footer d-none flex-column align-items-stretch gap-2">
                <div class="w-100 text-start">
                    <label for="cancel-reason" class="form-label small fw-semibold text-danger mb-1">Lý do hủy gửi cho khách</label>
                    <textarea id="cancel-reason" class="form-control form-control-sm" rows="2" maxlength="1000" placeholder="Ví dụ: Sân bảo trì đột xuất, xin lỗi quý khách..."></textarea>
                </div>
                <button type="button" id="cancel-booking" class="btn btn-danger w-100">Hủy đơn đã xác nhận</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales-all.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const venueFilter = document.getElementById('venue-filter');
        const courtFilter = document.getElementById('court-filter');
        const statusFilter = document.getElementById('status-filter');
        const dateJump = document.getElementById('date-jump');
        const goToday = document.getElementById('go-today');
        const agendaList = document.getElementById('agenda-list');
        const agendaRangeLabel = document.getElementById('agenda-range-label');
        const courtOptions = Array.from(courtFilter.options).slice(1);
        const detailModal = new bootstrap.Modal(document.getElementById('booking-detail-modal'));
        const bookingActions = document.getElementById('booking-actions');
        const actionAlert = document.getElementById('booking-action-alert');
        const confirmButton = document.getElementById('confirm-booking');
        const rejectButton = document.getElementById('reject-booking');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const statusUrlTemplate = @js(route('owner.web.calendar.bookings.status', ['booking' => '__BOOKING__']));
        const cancelUrlTemplate = @js(route('owner.web.calendar.bookings.cancel', ['booking' => '__BOOKING__']));
        const bookingCancel = document.getElementById('booking-cancel');
        const cancelReason = document.getElementById('cancel-reason');
        const cancelButton = document.getElementById('cancel-booking');
        const detailCancelLabel = document.getElementById('detail-cancel-label');
        const detailCancelReason = document.getElementById('detail-cancel-reason');
        let selectedBookingId = null;

        const statusClasses = {
            pending: 'status-pending',
            confirmed: 'status-confirmed',
            completed: 'status-completed',
            cancelled: 'status-cancelled',
            rejected: 'status-rejected',
        };

        const calendar = new FullCalendar.Calendar(document.getElementById('booking-calendar'), {
            locale: 'vi',
            initialView: window.innerWidth < 768 ? 'timeGridDay' : 'timeGridWeek',
            firstDay: 1,
            nowIndicator: true,
            height: 'auto',
            slotMinTime: '05:00:00',
            slotMaxTime: '24:00:00',
            allDaySlot: false,
            expandRows: true,
            navLinks: true,
            stickyHeaderDates: true,
            dayMaxEvents: 3,
            eventMaxStack: 4,
            slotEventOverlap: false,
            eventTimeFormat: { hour: '2-digit', minute: '2-digit', hour12: false },
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'timeGridDay,timeGridWeek,dayGridMonth,listWeek',
            },
            buttonText: {
                today: 'Hôm nay',
                day: 'Ngày',
                week: 'Tuần',
                month: 'Tháng',
                list: 'Danh sách',
            },
            events: {
                url: @js(route('owner.web.calendar.events')),
                extraParams: () => ({
                    venue_id: venueFilter.value,
                    court_id: courtFilter.value,
                    status: statusFilter.value,
                }),
                failure: () => {
                    agendaList.innerHTML = '<div class="agenda-empty text-danger">Không tải được dữ liệu lịch đặt. Vui lòng thử lại.</div>';
                },
            },
            eventContent: ({ event, view }) => {
                const booking = event.extendedProps;
                const isMonthView = view.type === 'dayGridMonth';
                const title = isMonthView
                    ? `${booking.time_label} · ${booking.court_name}`
                    : `${booking.time_label} · ${booking.court_name}`;
                const subtitle = booking.customer_name;

                return {
                    html: `
                        <div class="booking-event" title="${escapeHtml(booking.court_name)} - ${escapeHtml(booking.customer_name)}">
                            <span class="booking-event-title">${escapeHtml(title)}</span>
                            <span class="booking-event-subtitle">${escapeHtml(subtitle)}</span>
                        </div>
                    `,
                };
            },
            eventClick: ({ event }) => showBookingDetail(event),
            eventsSet: (events) => renderAgenda(events),
            datesSet: (info) => {
                agendaRangeLabel.textContent = `${formatDate(info.start)} - ${formatDate(addDays(info.end, -1))}`;
            },
        });

        function addDays(date, days) {
            const next = new Date(date);
            next.setDate(next.getDate() + days);
            return next;
        }

        function formatDate(date) {
            return new Intl.DateTimeFormat('vi-VN', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
            }).format(date);
        }

        function renderAgenda(events) {
            const sorted = [...events].sort((a, b) => a.start - b.start);

            if (sorted.length === 0) {
                agendaList.innerHTML = '<div class="agenda-empty">Không có booking nào trong khung thời gian này.</div>';
                return;
            }

            agendaList.innerHTML = sorted.map((event) => {
                const booking = event.extendedProps;
                const color = event.backgroundColor || '#64748b';

                return `
                    <article class="agenda-item" data-event-id="${event.id}" style="border-left-color:${color}">
                        <div class="time">${booking.date_label} · ${booking.time_label}</div>
                        <div class="title">${escapeHtml(booking.court_name)} - ${escapeHtml(booking.customer_name)}</div>
                        <div class="meta">${escapeHtml(booking.venue_name)}</div>
                        <div class="d-flex align-items-center justify-content-between gap-2 mt-2">
                            <span class="status-pill ${statusClasses[booking.status] || 'status-cancelled'}">${escapeHtml(booking.status_label)}</span>
                            <strong>${escapeHtml(booking.total_price)}</strong>
                        </div>
                    </article>
                `;
            }).join('');

            agendaList.querySelectorAll('.agenda-item').forEach((item) => {
                item.addEventListener('click', () => {
                    const event = calendar.getEventById(item.dataset.eventId);
                    if (event) {
                        showBookingDetail(event);
                    }
                });
            });
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function showBookingDetail(event) {
            const booking = event.extendedProps;
            
            // FIX QUAN TRỌNG: Gán ID đơn hàng để Lệnh Hủy biết đang thao tác trên đơn nào
            selectedBookingId = booking.booking_id;

            document.getElementById('booking-code').textContent = `Mã booking #${booking.booking_id}`;
            document.getElementById('detail-venue').textContent = booking.venue_name;
            document.getElementById('detail-court').textContent = booking.court_name;
            document.getElementById('detail-time').textContent = `${booking.date_label}, ${booking.time_label}`;
            document.getElementById('detail-customer').textContent = booking.customer_name;
            document.getElementById('detail-email').textContent = booking.customer_email;
            
            const phoneStr = booking.customer_phone || 'Chưa cập nhật SĐT';
            document.getElementById('detail-phone').textContent = phoneStr;
            const btnCall = document.getElementById('btn-call-customer');
            const btnZalo = document.getElementById('btn-zalo-customer');
            if (phoneStr !== 'Chưa cập nhật SĐT' && phoneStr !== '') {
                btnCall.href = `tel:${phoneStr}`;
                btnZalo.href = `https://zalo.me/${phoneStr}`; 
                btnCall.style.display = 'inline-flex';
                btnZalo.style.display = 'inline-flex';
            } else {
                btnCall.style.display = 'none';
                btnZalo.style.display = 'none';
            }

            document.getElementById('detail-price').textContent = booking.total_price;
            document.getElementById('detail-status').className = `status-pill ${statusClasses[booking.status] || 'status-cancelled'}`;
            document.getElementById('detail-status').textContent = booking.status_label;

            // FIX QUAN TRỌNG: Dùng ĐÚNG tên biến HTML của bạn để chống sập JS
            actionAlert.className = 'alert d-none';
            bookingActions.classList.add('d-none');
            bookingCancel.classList.add('d-none');
            
            if (cancelReason) cancelReason.value = '';

            if (booking.status === 'pending') {
                bookingActions.classList.remove('d-none');
            } else if (booking.status === 'confirmed') {
                bookingCancel.classList.remove('d-none');
            }

            const showCancelReason = booking.status === 'cancelled';
            detailCancelLabel.classList.toggle('d-none', !showCancelReason);
            detailCancelReason.classList.toggle('d-none', !showCancelReason);
            
            if (showCancelReason) {
                detailCancelReason.textContent = booking.cancel_reason || 'Khách tự hủy';
            }

            detailModal.show();
            // --- BẮT ĐẦU: RENDER DỊCH VỤ TRÊN MODAL CHỦ SÂN ---
            const svcs = booking.services || []; // Lấy mảng services từ JSON
            const svcLabel = document.getElementById('detail-services-label');
            const svcList = document.getElementById('detail-services-list');
            
            if (svcs.length > 0) {
                svcLabel.classList.remove('d-none');
                svcList.classList.remove('d-none');
                
                // Dùng Vanilla JS để sinh mã HTML danh sách món đồ
                svcList.innerHTML = svcs.map(s => {
                    const isRental = s.pricing_type === 'rental' 
                        ? '<span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle ms-1" style="font-size: 0.6rem;">Thuê</span>' 
                        : '<span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle ms-1" style="font-size: 0.6rem;">Mua</span>';
                    
                    const priceFmt = new Intl.NumberFormat('vi-VN').format(s.pivot.price) + 'đ';
                    
                    return `
                        <div class="mb-2 pb-2 border-bottom border-light text-sm">
                            <div class="fw-bold text-dark d-flex align-items-center mb-1">
                                <span class="text-truncate">${s.name}</span> ${isRental}
                            </div>
                            <div class="d-flex justify-content-between text-secondary" style="font-size: 0.85rem;">
                                <span>SL: <strong class="text-dark">${s.pivot.quantity}</strong> ${s.unit}</span>
                                <strong class="text-success">${priceFmt}</strong>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                svcLabel.classList.add('d-none');
                svcList.classList.add('d-none');
                svcList.innerHTML = '';
            }
            // --- KẾT THÚC: RENDER DỊCH VỤ ---
        }

        async function updateBookingStatus(status) {
            if (!selectedBookingId) return;

            const button = status === 'confirmed' ? confirmButton : rejectButton;
            const originalText = button.textContent;
            confirmButton.disabled = true;
            rejectButton.disabled = true;
            button.textContent = 'Đang xử lý...';

            try {
                const response = await fetch(
                    statusUrlTemplate.replace('__BOOKING__', selectedBookingId),
                    {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ status }),
                    }
                );
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Không thể cập nhật booking.');
                }

                const pendingBookingCount = document.getElementById('pending-booking-count');
                if (pendingBookingCount) {
                    pendingBookingCount.textContent = data.pending_count;
                }
                detailModal.hide();
                calendar.refetchEvents();
            } catch (error) {
                actionAlert.textContent = error.message;
                actionAlert.className = 'alert alert-danger';
            } finally {
                confirmButton.disabled = false;
                rejectButton.disabled = false;
                button.textContent = originalText;
            }
        }

        // THỰC THI TRƯỜNG HỢP A: CHỦ SÂN CHỦ ĐỘNG HỦY CA
        // Bổ sung tham số (event) để chặn hành vi mặc định của Form
        async function cancelConfirmedBooking(event) {
            // Ngăn chặn trình duyệt tự động validate popup HTML5 (Chống lỗi validate 2 lần)
            if (event) event.preventDefault();

            // Dùng đúng biến selectedBookingId của bạn
            if (!selectedBookingId) return;

            // Dùng đúng biến cancelReason của bạn
            const reason = cancelReason.value.trim();
            if (!reason) {
                actionAlert.textContent = 'Vui lòng nhập lý do hủy sân để thông báo cho khách!';
                actionAlert.className = 'alert alert-danger mt-3';
                actionAlert.classList.remove('d-none');
                cancelReason.focus(); // Tự động trỏ chuột vào ô nhập cho tiện
                return;
            }

            const originalText = cancelButton.innerHTML;
            cancelButton.disabled = true;
            cancelButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang hủy...';
            actionAlert.classList.add('d-none'); // Ẩn cảnh báo lỗi màu đỏ đi

            try {
                // Dùng đúng biến cancelUrlTemplate của bạn
                const response = await fetch(cancelUrlTemplate.replace('__BOOKING__', selectedBookingId), {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({ reason: reason })
                });
                
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Không thể hủy booking.');
                }

                alert(data.message);
                detailModal.hide();
                calendar.refetchEvents();
            } catch (error) {
                actionAlert.textContent = error.message;
                actionAlert.className = 'alert alert-danger mt-3';
                actionAlert.classList.remove('d-none');
            } finally {
                cancelButton.disabled = false;
                cancelButton.innerHTML = originalText;
            }
        }

        confirmButton.addEventListener('click', () => updateBookingStatus('confirmed'));
        rejectButton.addEventListener('click', () => updateBookingStatus('rejected'));
        cancelButton.addEventListener('click', cancelConfirmedBooking);

        venueFilter.addEventListener('change', () => {
            courtOptions.forEach((option) => {
                option.hidden = venueFilter.value !== '' && option.dataset.venue !== venueFilter.value;
            });

            const selectedCourt = courtFilter.selectedOptions[0];
            if (selectedCourt && selectedCourt.hidden) {
                courtFilter.value = '';
            }

            calendar.refetchEvents();
        });

        courtFilter.addEventListener('change', () => calendar.refetchEvents());
        statusFilter.addEventListener('change', () => calendar.refetchEvents());
        dateJump.addEventListener('change', () => {
            if (dateJump.value) {
                calendar.gotoDate(dateJump.value);
            }
        });
        goToday.addEventListener('click', () => calendar.today());

        calendar.render();
    });
</script>
@endpush
