<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Lịch đặt sân | SportHub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --calendar-border: #e4e4e7;
            --calendar-accent: #047857;
        }

        body {
            background: #f7f7f5;
            color: #18181b;
        }

        .page-shell {
            max-width: 1500px;
        }

        .summary-item {
            min-width: 140px;
            border-left: 3px solid var(--calendar-accent);
            padding-left: 12px;
        }

        .filter-bar,
        .calendar-panel {
            border: 1px solid var(--calendar-border);
            border-radius: 8px;
            background: #fff;
        }

        .calendar-panel {
            min-height: 680px;
        }

        .fc .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .fc .fc-button-primary {
            border-color: #d4d4d8;
            background: #fff;
            color: #3f3f46;
            box-shadow: none;
        }

        .fc .fc-button-primary:hover,
        .fc .fc-button-primary:focus {
            border-color: #a1a1aa;
            background: #f4f4f5;
            color: #18181b;
        }

        .fc .fc-button-primary:not(:disabled).fc-button-active {
            border-color: var(--calendar-accent);
            background: var(--calendar-accent);
            color: #fff;
        }

        .fc .fc-daygrid-day.fc-day-today,
        .fc .fc-timegrid-col.fc-day-today {
            background: #ecfdf5;
        }

        .fc-event {
            cursor: pointer;
            border-radius: 4px;
            padding: 1px 3px;
        }

        .legend-dot {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        @media (max-width: 767px) {
            .calendar-panel {
                min-height: 600px;
                overflow-x: auto;
            }

            #booking-calendar {
                min-width: 760px;
            }

            .fc .fc-toolbar {
                align-items: flex-start;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
<main class="container-fluid page-shell py-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <a href="{{ route('owner.web.venues.index') }}" class="small text-decoration-none text-success">
                ← Quản lý điểm sân
            </a>
            <h1 class="h3 fw-bold mt-2 mb-1">Lịch đặt sân</h1>
            <p class="text-secondary mb-0">Theo dõi lịch đặt theo ngày, tuần hoặc tháng.</p>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-4">
            <div class="d-flex gap-4">
                <div class="summary-item">
                    <div class="small text-secondary">Lịch hôm nay</div>
                    <div class="fs-4 fw-bold">{{ $todayBookings }}</div>
                </div>
                <div class="summary-item">
                    <div class="small text-secondary">Chờ xác nhận</div>
                    <div id="pending-booking-count" class="fs-4 fw-bold text-warning-emphasis">{{ $pendingBookings }}</div>
                </div>
            </div>
            <a href="{{ route('owner.web.venues.index') }}" class="btn btn-outline-success">
                Quản lý sân
            </a>
        </div>
    </div>

    <section class="filter-bar p-3 mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label for="venue-filter" class="form-label small fw-semibold">Điểm sân</label>
                <select id="venue-filter" class="form-select">
                    <option value="">Tất cả điểm sân</option>
                    @foreach ($venues as $venue)
                        <option value="{{ $venue->id }}">{{ $venue->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label for="court-filter" class="form-label small fw-semibold">Sân con</label>
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
            <div class="col-12 col-md-4">
                <label for="status-filter" class="form-label small fw-semibold">Trạng thái</label>
                <select id="status-filter" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending">Chờ xác nhận</option>
                    <option value="confirmed">Đã xác nhận</option>
                    <option value="completed">Đã hoàn thành</option>
                    <option value="cancelled">Đã hủy</option>
                    <option value="rejected">Đã từ chối</option>
                </select>
            </div>
        </div>
    </section>

    <div class="d-flex flex-wrap gap-3 small text-secondary mb-3" aria-label="Chú thích trạng thái">
        <span><i class="legend-dot me-1" style="background:#d97706"></i> Chờ xác nhận</span>
        <span><i class="legend-dot me-1" style="background:#047857"></i> Đã xác nhận</span>
        <span><i class="legend-dot me-1" style="background:#2563eb"></i> Đã hoàn thành</span>
        <span><i class="legend-dot me-1" style="background:#64748b"></i> Đã hủy</span>
        <span><i class="legend-dot me-1" style="background:#dc2626"></i> Đã từ chối</span>
    </div>

    <section class="calendar-panel p-2 p-md-3">
        <div id="booking-calendar"></div>
    </section>
</main>

<div class="modal fade" id="booking-detail-modal" tabindex="-1" aria-labelledby="booking-detail-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="small text-secondary" id="booking-code"></div>
                    <h2 class="modal-title fs-5" id="booking-detail-title">Chi tiết lịch đặt</h2>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div id="booking-action-alert" class="alert d-none" role="alert"></div>
                <dl class="row mb-0">
                    <dt class="col-4 text-secondary fw-normal">Điểm sân</dt>
                    <dd class="col-8 fw-semibold" id="detail-venue"></dd>
                    <dt class="col-4 text-secondary fw-normal">Sân con</dt>
                    <dd class="col-8" id="detail-court"></dd>
                    <dt class="col-4 text-secondary fw-normal">Thời gian</dt>
                    <dd class="col-8" id="detail-time"></dd>
                    <dt class="col-4 text-secondary fw-normal">Khách hàng</dt>
                    <dd class="col-8">
                        <div id="detail-customer"></div>
                        <small class="text-secondary" id="detail-email"></small>
                    </dd>
                    <dt class="col-4 text-secondary fw-normal">Trạng thái</dt>
                    <dd class="col-8"><span class="badge" id="detail-status"></span></dd>
                    <dt class="col-4 text-secondary fw-normal">Tổng tiền</dt>
                    <dd class="col-8 fw-semibold" id="detail-price"></dd>
                    <dt class="col-4 text-secondary fw-normal">Ghi chú</dt>
                    <dd class="col-8 mb-0" id="detail-note"></dd>
                    <dt class="col-4 text-secondary fw-normal d-none" id="detail-cancel-label">Lý do hủy</dt>
                    <dd class="col-8 mb-0 text-danger fw-semibold d-none" id="detail-cancel-reason"></dd>
                </dl>
            </div>
            <div id="booking-actions" class="modal-footer d-none">
                <button type="button" id="reject-booking" class="btn btn-outline-danger">Từ chối</button>
                <button type="button" id="confirm-booking" class="btn btn-success">Xác nhận</button>
            </div>
            <div id="booking-cancel" class="modal-footer d-none flex-column align-items-stretch gap-2">
                <div class="w-100 text-start">
                    <label for="cancel-reason" class="form-label small fw-semibold text-danger mb-1">Lý do hủy (gửi cho khách)</label>
                    <textarea id="cancel-reason" class="form-control form-control-sm" rows="2" maxlength="1000" placeholder="Ví dụ: Sân bảo trì đột xuất, xin lỗi quý khách..."></textarea>
                </div>
                <button type="button" id="cancel-booking" class="btn btn-danger w-100">Hủy đơn đã xác nhận</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales-all.global.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const venueFilter = document.getElementById('venue-filter');
        const courtFilter = document.getElementById('court-filter');
        const statusFilter = document.getElementById('status-filter');
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
            pending: 'text-bg-warning',
            confirmed: 'text-bg-success',
            completed: 'text-bg-primary',
            cancelled: 'text-bg-secondary',
            rejected: 'text-bg-danger',
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
                month: 'Tháng',
            },
            events: {
                url: @js(route('owner.web.calendar.events')),
                extraParams: () => ({
                    venue_id: venueFilter.value,
                    court_id: courtFilter.value,
                    status: statusFilter.value,
                }),
                failure: () => {
                    window.alert('Không tải được dữ liệu lịch đặt. Vui lòng thử lại.');
                },
            },
            eventClick: ({ event }) => {
                const booking = event.extendedProps;
                document.getElementById('booking-code').textContent = `Mã booking #${booking.booking_id}`;
                document.getElementById('detail-venue').textContent = booking.venue_name;
                document.getElementById('detail-court').textContent = booking.court_name;
                document.getElementById('detail-time').textContent = `${booking.date_label}, ${booking.time_label}`;
                document.getElementById('detail-customer').textContent = booking.customer_name;
                document.getElementById('detail-email').textContent = booking.customer_email;
                document.getElementById('detail-price').textContent = booking.total_price;
                document.getElementById('detail-note').textContent = booking.note || 'Không có';

                const status = document.getElementById('detail-status');
                status.textContent = booking.status_label;
                status.className = `badge ${statusClasses[booking.status] || 'text-bg-secondary'}`;
                selectedBookingId = booking.booking_id;
                bookingActions.classList.toggle('d-none', booking.status !== 'pending');
                bookingCancel.classList.toggle('d-none', booking.status !== 'confirmed');
                cancelReason.value = '';

                const showCancelReason = booking.status === 'cancelled' && !!booking.cancel_reason;
                detailCancelLabel.classList.toggle('d-none', !showCancelReason);
                detailCancelReason.classList.toggle('d-none', !showCancelReason);
                detailCancelReason.textContent = booking.cancel_reason || '';

                actionAlert.className = 'alert d-none';
                actionAlert.textContent = '';
                detailModal.show();
            },
        });

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

                document.getElementById('pending-booking-count').textContent = data.pending_count;
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

        async function cancelConfirmedBooking() {
            if (!selectedBookingId) return;

            const reason = cancelReason.value.trim();
            if (!reason) {
                actionAlert.textContent = 'Vui lòng nhập lý do hủy.';
                actionAlert.className = 'alert alert-danger';
                return;
            }

            const originalText = cancelButton.textContent;
            cancelButton.disabled = true;
            cancelButton.textContent = 'Đang hủy...';

            try {
                const response = await fetch(
                    cancelUrlTemplate.replace('__BOOKING__', selectedBookingId),
                    {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ reason }),
                    }
                );
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Không thể hủy booking.');
                }

                detailModal.hide();
                calendar.refetchEvents();
            } catch (error) {
                actionAlert.textContent = error.message;
                actionAlert.className = 'alert alert-danger';
            } finally {
                cancelButton.disabled = false;
                cancelButton.textContent = originalText;
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
        calendar.render();
    });
</script>
</body>
</html>
