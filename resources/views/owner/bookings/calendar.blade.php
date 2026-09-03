@extends('owner.layoutOwner.app')

@section('title','Lịch đặt sân - SportHub')

@section('content')
<style>
    /* ĐỒNG BỘ TÔNG MÀU SPORTHUB & TỐI ƯU UX */
    :root {
        --brand-emerald: #059669;
        --brand-emerald-dark: #047857;
        --brand-emerald-light: #ecfdf5;
        --fc-border-color: #e2e8f0;
        --fc-button-bg-color: #059669;
        --fc-button-border-color: #059669;
        --fc-button-hover-bg-color: #047857;
        --fc-button-hover-border-color: #047857;
        --fc-button-active-bg-color: #047857;
        --fc-button-active-border-color: #047857;
        --fc-today-bg-color: #ecfdf5;
    }
    body { background-color: #f8fafc; }
    
    /* Stats Card */
    .stat-card-custom {
        background: #fff; border-radius: 16px; padding: 20px; border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); transition: transform 0.2s ease;
    }
    .stat-card-custom:hover { transform: translateY(-3px); box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
    
    /* Layout Song Song (Side-by-side) */
    .schedule-wrapper { display: flex; gap: 1.5rem; height: calc(100vh - 180px); min-height: 700px; margin-top: 1.5rem; align-items: stretch; }
    .calendar-pane { flex: 1; min-width: 0; background: #fff; border-radius: 16px; padding: 1.5rem; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden; display: flex; flex-direction: column; }
    .agenda-pane { width: 380px; flex-shrink: 0; background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); display: flex; flex-direction: column; overflow: hidden; }
    
    /* Danh sách Agenda Scrollable */
    .agenda-header { background: #f8fafc; padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; }
    .agenda-list-scroll { flex: 1; overflow-y: auto; padding: 1rem; background: #f1f5f9; }
    
    /* Thẻ Ticket Agenda tối ưu quan sát nhanh */
    .agenda-ticket {
        background: #fff; border-radius: 12px; padding: 1rem; margin-bottom: 0.875rem;
        border-left: 5px solid var(--brand-emerald); box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        cursor: pointer; transition: all 0.2s ease;
    }
    .agenda-ticket:hover { transform: translateY(-2px); box-shadow: 0 8px 12px rgba(0,0,0,0.1); border-color: var(--brand-emerald-dark); }
    
    /* Trạng thái */
    .status-badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
    .status-confirmed { background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .status-pending { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
    .status-completed { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .status-cancelled { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }
    .status-rejected { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

    /* Fix FullCalendar UI */
    .fc-event { cursor: pointer; border-radius: 4px; border: none; box-shadow: 0 1px 2px rgba(0,0,0,0.15); }
    .fc .fc-toolbar-title { font-size: 1.25rem; font-weight: 800; color: #1e293b; }
    .fc-timegrid-slot { height: 3em !important; } /* Tăng chiều cao các ô để dễ click */
    
    @media (max-width: 992px) {
        .schedule-wrapper { flex-direction: column; height: auto; }
        .agenda-pane { width: 100%; height: 500px; }
    }
</style>

<main class="container-fluid px-4 py-4">
    
    <!-- HEADER -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
        <div>
            <h1 class="h3 fw-bolder text-dark mb-1">Lịch trình đặt sân</h1>
            <p class="text-secondary mb-0">Quản lý các ca đá và xử lý yêu cầu đặt sân trong ngày.</p>
        </div>
        <div class="d-flex gap-2 mt-3 mt-md-0">
            <button type="button" class="btn btn-outline-success border-2 fw-bold" id="go-today">
                <i class="fa-regular fa-calendar-check me-1"></i> Hôm nay
            </button>
            <a href="{{ route('owner.web.venues.index') }}" class="btn btn-success fw-bold" style="background-color: #059669; border-color: #059669;">
                <i class="fa-solid fa-layer-group me-1"></i> Quản lý điểm sân
            </a>
        </div>
    </div>

    <!-- STATS GRID -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="stat-card-custom d-flex align-items-center gap-3">
                <div class="stat-icon bg-success bg-opacity-10 text-success"><i class="fa-solid fa-calendar-day"></i></div>
                <div>
                    <div class="text-secondary small fw-semibold">Lịch hôm nay</div>
                    <div class="fs-4 fw-black text-dark" id="today-booking-count">{{ $todayBookings }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card-custom d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fa-solid fa-circle-check"></i></div>
                <div>
                    <div class="text-secondary small fw-semibold">Đã xác nhận</div>
                    <div class="fs-4 fw-black text-primary">{{ $confirmedBookings }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card-custom d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fa-solid fa-calendar-week"></i></div>
                <div>
                    <div class="text-secondary small fw-semibold">Tuần này</div>
                    <div class="fs-4 fw-black text-dark">{{ $weekBookings }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card-custom d-flex align-items-center gap-3">
                <div class="stat-icon bg-info bg-opacity-10 text-info"><i class="fa-solid fa-map-location-dot"></i></div>
                <div>
                    <div class="text-secondary small fw-semibold">Tổng sân con</div>
                    <div class="fs-4 fw-black text-dark">{{ $totalCourts }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- BỘ LỌC (Thu gọn thành 1 hàng ngang) -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body p-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label for="venue-filter" class="form-label small fw-bold text-secondary mb-1">Điểm sân</label>
                    <select id="venue-filter" class="form-select bg-light">
                        <option value="">-- Tất cả điểm sân --</option>
                        @foreach ($venues as $venue)
                            <option value="{{ $venue->id }}">{{ $venue->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="court-filter" class="form-label small fw-bold text-secondary mb-1">Sân con</label>
                    <select id="court-filter" class="form-select bg-light">
                        <option value="">-- Tất cả sân con --</option>
                        @foreach ($venues as $venue)
                            @foreach ($venue->courts as $court)
                                <option value="{{ $court->id }}" data-venue="{{ $venue->id }}">
                                    {{ $venue->name }} - {{ $court->name }}
                                </option>
                            @endforeach
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status-filter" class="form-label small fw-bold text-secondary mb-1">Trạng thái</label>
                    <select id="status-filter" class="form-select bg-light">
                        <option value="">-- Tất cả trạng thái --</option>
                        <option value="confirmed">Đã xác nhận</option>
                        <option value="completed">Đã hoàn thành</option>
                        <option value="cancelled">Đã hủy</option>
                        <option value="rejected">Đã từ chối</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date-jump" class="form-label small fw-bold text-secondary mb-1">Chuyển đến ngày</label>
                    <input id="date-jump" type="date" class="form-control bg-light">
                </div>
            </div>
        </div>
    </div>

    <!-- KHU VỰC LỊCH VÀ DANH SÁCH (SIDE-BY-SIDE) -->
    <div class="schedule-wrapper">
        <!-- Bên Trái: FullCalendar -->
        <div class="calendar-pane">
            <div id="booking-calendar" style="height: 100%;"></div>
        </div>

        <!-- Bên Phải: Danh sách cuộn độc lập -->
        <aside class="agenda-pane">
            <div class="agenda-header">
                <h2 class="h6 fw-bolder text-dark mb-1"><i class="fa-solid fa-list-ul text-success me-2"></i>Chi tiết ca đá</h2>
                <p id="agenda-range-label" class="text-secondary small mb-0">Đang tải dữ liệu...</p>
            </div>
            <!-- Khu vực này có thanh cuộn riêng, giải quyết vấn đề nhiều đơn đặt sân -->
            <div id="agenda-list" class="agenda-list-scroll">
                <div class="text-center py-5 text-secondary">
                    <div class="spinner-border spinner-border-sm mb-2" role="status"></div>
                    <div>Đang tải lịch...</div>
                </div>
            </div>
        </aside>
    </div>
</main>

<!-- MODAL CHI TIẾT (Giữ nguyên IDs) -->
<div class="modal fade" id="booking-detail-modal" tabindex="-1" aria-labelledby="booking-detail-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-bottom-0 pb-0">
                <div>
                    <div class="badge bg-success bg-opacity-10 text-success mb-2 border border-success-subtle" id="booking-code"></div>
                    <h2 class="modal-title fs-5 fw-bold text-dark" id="booking-detail-title">Chi tiết lịch đặt</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body pt-3">
                <div id="booking-action-alert" class="alert d-none" role="alert"></div>
                
                <div class="bg-light rounded-3 p-3 mb-3 border border-secondary-subtle">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-secondary small fw-semibold">Thời gian đá:</span>
                        <span id="detail-time" class="fw-bold text-success fs-6"></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-secondary small fw-semibold">Sân con:</span>
                        <span id="detail-court" class="fw-bold text-dark"></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <span class="text-secondary small fw-semibold">Điểm sân:</span>
                        <span id="detail-venue" class="text-dark small text-end"></span>
                    </div>
                </div>

                <dl class="row mb-0">
                    <dt class="col-sm-4 text-secondary fw-normal">Khách hàng</dt>
                    <dd class="col-sm-8">
                        <div id="detail-customer" class="fw-bold text-dark"></div>
                        <small class="text-secondary d-block" id="detail-email"></small>
                        <div class="mt-2 d-flex align-items-center gap-2">
                            <span id="detail-phone" class="badge bg-light text-dark border border-secondary-subtle fs-6"></span>
                            <a href="#" id="btn-call-customer" class="btn btn-sm btn-success px-2 py-1" title="Gọi điện">
                                <i class="fa-solid fa-phone"></i>
                            </a>
                            <a href="#" target="_blank" id="btn-zalo-customer" class="btn btn-sm btn-primary px-2 py-1" style="background-color: #0068ff; border-color: #0068ff;" title="Chat Zalo">
                                <span style="font-size: 11px; font-weight: 900;">Zalo</span>
                            </a>
                        </div>
                    </dd>

                    <dt class="col-sm-4 text-secondary fw-normal mt-3">Trạng thái</dt>
                    <dd class="col-sm-8 mt-3"><span id="detail-status"></span></dd>
                    
                    <!-- Dịch vụ kèm -->
                    <dt id="detail-services-label" class="col-sm-4 text-secondary fw-normal mt-3 d-none">Dịch vụ</dt>
                    <dd id="detail-services-list" class="col-sm-8 mt-3 d-none"></dd>

                    <dt class="col-sm-4 text-secondary fw-normal mt-3">Tổng tiền</dt>
                    <dd class="col-sm-8 mt-3"><strong id="detail-price" class="text-danger fs-5"></strong></dd>

                    <dt class="col-sm-4 text-danger fw-normal mt-3 d-none" id="detail-cancel-label">Lý do hủy</dt>
                    <dd class="col-sm-8 mt-3 d-none text-danger fw-bold" id="detail-cancel-reason"></dd>
                </dl>
            </div>

            <!-- Nút hành động -->
            <div id="booking-actions" class="modal-footer bg-light border-top-0 d-none">
                <button type="button" id="reject-booking" class="btn btn-outline-danger fw-bold">Từ chối</button>
                <button type="button" id="confirm-booking" class="btn btn-success fw-bold px-4" style="background-color: #059669;">Xác nhận Đơn</button>
            </div>
            
            <div id="booking-cancel" class="modal-footer bg-light border-top-0 d-none flex-column align-items-stretch gap-2">
                <div class="w-100 text-start">
                    <label for="cancel-reason" class="form-label small fw-bold text-danger mb-1"><i class="fa-solid fa-triangle-exclamation me-1"></i> Lý do hủy gửi cho khách</label>
                    <textarea id="cancel-reason" class="form-control" rows="2" maxlength="1000" placeholder="Ví dụ: Sân bảo trì đột xuất, xin lỗi quý khách..."></textarea>
                </div>
                <button type="button" id="cancel-booking" class="btn btn-danger w-100 fw-bold">Hủy đơn đã xác nhận</button>
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
            initialView: window.innerWidth < 992 ? 'timeGridDay' : 'timeGridWeek',
            firstDay: 1,
            nowIndicator: true,
            height: '100%',
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
                right: 'timeGridDay,timeGridWeek,dayGridMonth',
            },
            buttonText: {
                today: 'Hôm nay',
                day: 'Ngày',
                week: 'Tuần',
                month: 'Tháng'
            },
            events: {
                url: @js(route('owner.web.calendar.events')),
                extraParams: () => ({
                    venue_id: venueFilter.value,
                    court_id: courtFilter.value,
                    status: statusFilter.value,
                }),
                failure: () => {
                    agendaList.innerHTML = '<div class="text-center py-5 text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation mb-2 fs-2"></i><br>Không tải được dữ liệu lịch đặt.</div>';
                },
            },
            eventContent: ({ event, view }) => {
                const booking = event.extendedProps;
                return {
                    html: `
                        <div class="p-1 h-100 d-flex flex-column" title="${escapeHtml(booking.court_name)} - ${escapeHtml(booking.customer_name)}">
                            <div class="fw-bold text-truncate" style="font-size: 11px;">${booking.time_label}</div>
                            <div class="text-truncate fw-bolder" style="font-size: 12px;">${escapeHtml(booking.court_name)}</div>
                            <div class="text-truncate opacity-75" style="font-size: 10px;">${escapeHtml(booking.customer_name)}</div>
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
            return new Intl.DateTimeFormat('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' }).format(date);
        }

        // TỐI ƯU HÓA CARD AGENDA (Hiển thị to rõ Sân & Giờ)
        function renderAgenda(events) {
            const sorted = [...events].sort((a, b) => a.start - b.start);

            if (sorted.length === 0) {
                agendaList.innerHTML = '<div class="text-center py-5 text-secondary"><i class="fa-solid fa-mug-hot mb-2 fs-1 opacity-50"></i><br>Không có lịch đặt nào.</div>';
                return;
            }

            agendaList.innerHTML = sorted.map((event) => {
                const booking = event.extendedProps;
                const color = event.backgroundColor || '#059669';

                return `
                    <div class="agenda-ticket" data-event-id="${event.id}" style="border-left-color:${color}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="fw-black text-dark" style="font-size: 16px;"><i class="fa-regular fa-clock text-success me-1"></i> ${booking.time_label}</div>
                            <span class="status-badge ${statusClasses[booking.status] || 'status-cancelled'}">${escapeHtml(booking.status_label)}</span>
                        </div>
                        <div class="fw-bold text-dark mb-1" style="font-size: 14px;">${escapeHtml(booking.court_name)}</div>
                        <div class="text-secondary mb-2" style="font-size: 12px;"><i class="fa-regular fa-user me-1"></i> ${escapeHtml(booking.customer_name)}</div>
                        <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top border-light">
                            <span class="text-muted text-truncate" style="font-size: 11px; max-width: 60%;">${booking.date_label}</span>
                            <strong class="text-success">${escapeHtml(booking.total_price)}</strong>
                        </div>
                    </div>
                `;
            }).join('');

            agendaList.querySelectorAll('.agenda-ticket').forEach((item) => {
                item.addEventListener('click', () => {
                    const event = calendar.getEventById(item.dataset.eventId);
                    if (event) showBookingDetail(event);
                });
            });
        }

        function escapeHtml(value) {
            return String(value ?? '').replaceAll('&', '&amp;').replaceAll('<', '&lt;').replaceAll('>', '&gt;').replaceAll('"', '&quot;').replaceAll("'", '&#039;');
        }

        // --- Logic JS giữ nguyên hoàn toàn ---
        function showBookingDetail(event) {
            const booking = event.extendedProps;
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
            
            const statusEl = document.getElementById('detail-status');
            statusEl.className = `status-badge px-2 py-1 fs-6 ${statusClasses[booking.status] || 'status-cancelled'}`;
            statusEl.textContent = booking.status_label;

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

            const svcs = booking.services || [];
            const svcLabel = document.getElementById('detail-services-label');
            const svcList = document.getElementById('detail-services-list');
            
            if (svcs.length > 0) {
                svcLabel.classList.remove('d-none');
                svcList.classList.remove('d-none');
                svcList.innerHTML = svcs.map(s => {
                    const isRental = s.pricing_type === 'rental' ? '<span class="badge bg-primary bg-opacity-10 text-primary ms-1">Thuê</span>' : '<span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">Mua</span>';
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

            detailModal.show();
        }

        async function updateBookingStatus(status) {
            if (!selectedBookingId) return;
            const button = status === 'confirmed' ? confirmButton : rejectButton;
            const originalText = button.textContent;
            confirmButton.disabled = true;
            rejectButton.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang xử lý...';

            try {
                const response = await fetch(statusUrlTemplate.replace('__BOOKING__', selectedBookingId), {
                    method: 'PATCH',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ status })
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Không thể cập nhật booking.');

                const pendingBookingCount = document.getElementById('pending-booking-count');
                if (pendingBookingCount) pendingBookingCount.textContent = data.pending_count;
                detailModal.hide();
                calendar.refetchEvents();
            } catch (error) {
                actionAlert.textContent = error.message;
                actionAlert.className = 'alert alert-danger mb-3';
                actionAlert.classList.remove('d-none');
            } finally {
                confirmButton.disabled = false;
                rejectButton.disabled = false;
                button.textContent = originalText;
            }
        }

        async function cancelConfirmedBooking(event) {
            if (event) event.preventDefault();
            if (!selectedBookingId) return;

            const reason = cancelReason.value.trim();
            if (!reason) {
                actionAlert.textContent = 'Vui lòng nhập lý do hủy sân để thông báo cho khách!';
                actionAlert.className = 'alert alert-danger mb-3';
                actionAlert.classList.remove('d-none');
                cancelReason.focus();
                return;
            }

            const originalText = cancelButton.innerHTML;
            cancelButton.disabled = true;
            cancelButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Đang hủy...';
            actionAlert.classList.add('d-none');

            try {
                const response = await fetch(cancelUrlTemplate.replace('__BOOKING__', selectedBookingId), {
                    method: 'PATCH',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ reason: reason })
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Không thể hủy booking.');

                alert(data.message);
                detailModal.hide();
                calendar.refetchEvents();
            } catch (error) {
                actionAlert.textContent = error.message;
                actionAlert.className = 'alert alert-danger mb-3';
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
            if (selectedCourt && selectedCourt.hidden) courtFilter.value = '';
            calendar.refetchEvents();
        });

        courtFilter.addEventListener('change', () => calendar.refetchEvents());
        statusFilter.addEventListener('change', () => calendar.refetchEvents());
        dateJump.addEventListener('change', () => { if (dateJump.value) calendar.gotoDate(dateJump.value); });
        goToday.addEventListener('click', () => { calendar.today(); dateJump.value = ''; });

        calendar.render();
    });
</script>
@endpush