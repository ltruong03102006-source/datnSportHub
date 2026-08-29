<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sport Booking - Admin Dashboard</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary: #2ecc71; /* Main Green */
            --primary-light: #eafaf1;
            --sidebar-bg: #ffffff;
            --body-bg: #f8f9fa;
            --text-dark: #2c3e50;
            --text-muted: #7f8c8d;
            --border-color: #ecf0f1;
            --card-bg: #ffffff;
            --radius-md: 12px;
            --radius-lg: 16px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--body-bg);
            color: var(--text-dark);
            display: flex;
            min-height: 100vh;
        }

        /* ----- SIDEBAR ----- */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 100;
        }

        .sidebar-logo {
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-color);
        }

        .sidebar-logo .logo-icon {
            width: 40px;
            height: 40px;
            background-color: var(--primary);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .sidebar-logo .logo-text {
            font-weight: 700;
            font-size: 16px;
            color: var(--text-dark);
            line-height: 1.2;
        }
        .sidebar-logo .logo-text span {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .sidebar-nav {
            flex: 1;
            padding: 20px 16px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            border-radius: 8px;
            transition: all 0.2s ease;
            gap: 14px;
        }

        .nav-item i {
            font-size: 16px;
            width: 20px;
            text-align: center;
        }

        .nav-item:hover {
            background-color: #f1f2f6;
            color: var(--text-dark);
        }

        .nav-item.active {
            background-color: var(--primary-light);
            color: var(--primary);
            font-weight: 600;
            border-left: 4px solid var(--primary);
        }

        .sidebar-footer {
            padding: 20px 16px;
            border-top: 1px solid var(--border-color);
        }

        .admin-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }

        .admin-profile img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
        }

        .admin-profile-info h6 {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 2px;
        }
        .admin-profile-info span {
            font-size: 11px;
            color: var(--text-muted);
        }

        /* ----- MAIN CONTENT ----- */
        .main-wrapper {
            flex: 1;
            margin-left: 260px;
            display: flex;
            flex-direction: column;
        }

        /* ----- HEADER ----- */
        .top-header {
            height: 70px;
            background-color: var(--sidebar-bg);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            position: sticky;
            top: 0;
            z-index: 99;
        }

        .search-bar {
            position: relative;
            width: 300px;
        }

        .search-bar i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 14px;
        }

        .search-bar input {
            width: 100%;
            padding: 10px 16px 10px 40px;
            border: none;
            background-color: #f1f3f5;
            border-radius: 20px;
            font-size: 13px;
            outline: none;
            color: var(--text-dark);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        /* ----- NOTIFICATION DROPDOWN ----- */
        .admin-notification {
            position: relative;
        }

        .admin-notification__button {
            background: transparent;
            border: none;
            position: relative;
            color: var(--text-muted);
            font-size: 18px;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: background-color 0.2s, color 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .admin-notification__button:hover {
            background-color: #f1f2f6;
            color: var(--text-dark);
        }

        .admin-notification__badge {
            position: absolute;
            top: 2px;
            right: 2px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            background-color: #e74c3c;
            color: white;
            font-size: 10px;
            font-weight: 700;
            border-radius: 999px;
            border: 2px solid white;
            display: none;
            line-height: 14px;
            text-align: center;
        }

        .admin-notification__dropdown {
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            width: 360px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            z-index: 1000;
        }

        .admin-notification__dropdown[hidden] {
            display: none;
        }

        .admin-notification__head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-color);
            background: #fdfdfd;
        }

        .admin-notification__title {
            margin: 0;
            font-weight: 700;
            font-size: 14px;
            color: var(--text-dark);
        }

        .admin-notification__action {
            border: none;
            background: transparent;
            color: var(--primary);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .admin-notification__action:hover {
            text-decoration: underline;
        }

        .admin-notification__list {
            max-height: 340px;
            overflow-y: auto;
        }

        .admin-notification__item {
            display: block;
            padding: 12px 16px;
            border-bottom: 1px solid #f1f2f6;
            color: inherit;
            text-decoration: none;
            transition: background 0.15s ease;
        }

        .admin-notification__item:hover {
            background-color: #f8f9fa;
            text-decoration: none;
        }

        .admin-notification__item.is-unread {
            background-color: #eafaf1;
            border-left: 3px solid var(--primary);
        }

        .admin-notification__item-title {
            margin: 0;
            font-weight: 600;
            font-size: 13px;
            color: var(--text-dark);
        }

        .admin-notification__item-content {
            margin: 4px 0 0;
            font-size: 12px;
            color: var(--text-muted);
            line-height: 1.4;
        }

        .admin-notification__item-time {
            margin: 4px 0 0;
            font-size: 11px;
            color: #bdc3c7;
        }

        .admin-notification__empty {
            padding: 24px 16px;
            text-align: center;
            color: var(--text-muted);
            font-size: 13px;
        }

        .admin-notification__foot {
            padding: 10px 16px;
            text-align: center;
            border-top: 1px solid var(--border-color);
            background: #fdfdfd;
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
        }

        /* ----- CONTENT AREA ----- */
        .content-area {
            padding: 30px 40px;
            flex: 1;
        }

        /* Generic Utilities */
        .card-custom {
            background: var(--card-bg);
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        }

        a { text-decoration: none; }
        ul { list-style: none; }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon">
                <i class="fa-solid fa-futbol"></i>
            </div>
            <div class="logo-text">
                Sport Booking<br>
                <span>FACILITY MANAGEMENT</span>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-pie"></i> Tổng quan
            </a>
            <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fa-solid fa-user-group"></i> Quản lý người dùng
            </a>
            <a href="{{ route('admin.venues.index') }}"
               class="nav-item {{ request()->routeIs('admin.venues.*') ? 'active' : '' }}"
               style="display:flex; align-items:center; width:100%; text-decoration:none;">
                <i class="fa-solid fa-building"></i> Quản lý cơ sở
            </a>
            <a href="{{ route('admin.venue-transfers.index') }}" class="nav-item {{ request()->routeIs('admin.venue-transfers.*') ? 'active' : '' }}">
                <i class="fa-solid fa-arrow-right-arrow-left"></i> Chuyển nhượng cơ sở
            </a>
            <a href="{{ route('admin.courts.index') }}" class="nav-item {{ request()->routeIs('admin.courts.*') ? 'active' : '' }}">
                <i class="fa-solid fa-list-check"></i> Quản lý sân
            </a>
            <a href="{{ route('admin.bookings.index') }}" class="nav-item {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                <i class="fa-regular fa-calendar-check"></i> Quản lý đặt sân
            </a>
            <a href="{{ route('admin.packages.index') }}" class="nav-item {{ request()->routeIs('admin.packages.*') ? 'active' : '' }}">
                <i class="fa-solid fa-layer-group"></i> Quản lý gói
            </a>
            <a href="{{ route('admin.chatbot.index') }}" class="nav-item {{ request()->routeIs('admin.chatbot.*') ? 'active' : '' }}">
                <i class="fa-solid fa-robot"></i> Chatbot logs
            </a>
            <a href="{{ route('admin.transactions.index') }}" class="nav-item {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
                <i class="fa-solid fa-wallet"></i> Lịch sử giao dịch
            </a>
            <a href="{{ route('admin.finance.index') }}" class="nav-item {{ request()->routeIs('admin.finance.*') ? 'active' : '' }}">
                <i class="fa-solid fa-chart-line"></i> Tổng quan tài chính
            </a>
            <a href="{{ route('admin.user_wallets.index') }}" class="nav-item {{ request()->routeIs('admin.user_wallets.*') ? 'active' : '' }}">
                <i class="fa-solid fa-wallet"></i> Quản lý ví người dùng
            </a>
            {{-- <a href="{{ route('admin.debts.index') }}" class="nav-item {{ request()->routeIs('admin.debts.*') ? 'active' : '' }}">
                <i class="fa-solid fa-scale-balanced"></i> Quản lý công nợ
            </a> --}}
            <a href="{{ route('admin.withdrawals.index') }}" class="nav-item {{ request()->routeIs('admin.withdrawals.*') ? 'active' : '' }}">
                <i class="fa-solid fa-money-bill-transfer"></i> Yêu cầu rút tiền
            </a>
            <!-- THÊM NÚT NÀY VÀO ĐÂY -->
            <a href="{{ route('admin.financial-settings.index') }}" class="nav-item {{ request()->routeIs('admin.financial-settings.*') ? 'active' : '' }}">
                <i class="fa-solid fa-money-bill-transfer"></i> Cấu hình tài chính
            </a>
            <!-- Mock links matching the screenshot -->
            {{-- <a href="#" class="nav-item"><i class="fa-solid fa-wallet"></i> Thanh toán</a>
            <a href="#" class="nav-item"><i class="fa-regular fa-star"></i> Đánh giá</a> --}}
            <a href="{{ route('admin.reports.index') }}" class="nav-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <i class="fa-solid fa-triangle-exclamation"></i> Báo cáo vi phạm
            </a>
            <a href="{{ route('admin.settings.index') }}" class="nav-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="fa-solid fa-gear"></i> Cài đặt hệ thống
            </a>
            {{-- <a href="#" class="nav-item"><i class="fa-regular fa-bell"></i> Thông báo</a>
            <a href="#" class="nav-item"><i class="fa-solid fa-chart-simple"></i> Thống kê</a> --}}
        </nav>

        <div class="sidebar-footer">
            <div class="admin-profile">
                <!-- Avatar placeholder -->
                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name ?? 'Admin') }}&background=2ecc71&color=fff" alt="Admin">
                <div class="admin-profile-info">
                    <h6>{{ Auth::user()->name ?? 'Admin Name' }}</h6>
                    <span>Hệ thống tối cao</span>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Wrapper -->
    <main class="main-wrapper">
        <!-- Top Header -->
        <header class="top-header">
            <div class="search-bar">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" placeholder="Tìm kiếm hệ thống...">
            </div>

            <div class="header-actions">
                <div class="admin-notification" id="admin-notification-root">
                    <button type="button" class="admin-notification__button" id="admin-notification-button" aria-label="Thông báo">
                        <i class="fa-regular fa-bell"></i>
                        <span class="admin-notification__badge" id="admin-notification-badge">0</span>
                    </button>

                    <div class="admin-notification__dropdown" id="admin-notification-dropdown" hidden>
                        <div class="admin-notification__head">
                            <p class="admin-notification__title">Thông báo hệ thống</p>
                            <button type="button" class="admin-notification__action" id="admin-notification-read-all">
                                Đánh dấu đã đọc
                            </button>
                        </div>

                        <div class="admin-notification__list" id="admin-notification-list">
                            <div class="admin-notification__empty">Đang tải...</div>
                        </div>

                        <div class="admin-notification__foot">
                            <a href="{{ route('admin.venues.index') }}" class="admin-notification__action">Danh sách cơ sở cần duyệt</a>
                        </div>
                    </div>
                </div>
                
                <div class="header-user">
                    <span>{{ Auth::user()->name ?? 'Admin Name' }}</span>
                    <i class="fa-solid fa-chevron-down" style="font-size: 10px; color: #7f8c8d;"></i>
                </div>
                
                <!-- Nút đăng xuất nhỏ giấu kế bên (Thường nhét vào dropdown nhưng làm nút rời tạm để có thể đăng xuất) -->
                <form action="{{ route('admin.logout') }}" method="POST" style="margin-left: 10px;">
                    @csrf
                    <button type="submit" style="background:none; border:none; color: #e74c3c; cursor: pointer; font-size: 13px; font-weight: 500;">
                        Đăng xuất
                    </button>
                </form>
            </div>
        </header>

        <!-- Content Area -->
        <div class="content-area">
            @yield('content')
        </div>
    </main>

    @stack('scripts')
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    @auth
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const root = document.getElementById('admin-notification-root');
                const button = document.getElementById('admin-notification-button');
                const dropdown = document.getElementById('admin-notification-dropdown');
                const list = document.getElementById('admin-notification-list');
                const badge = document.getElementById('admin-notification-badge');
                const readAllButton = document.getElementById('admin-notification-read-all');
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                if (!root || !button || !dropdown || !list || !badge) return;

                const latestUrl = @json(route('notifications.latest', ['context' => 'admin']));
                const unreadCountUrl = @json(route('notifications.unread-count', ['context' => 'admin']));
                const markAllReadUrl = @json(route('notifications.read-all', ['context' => 'admin']));
                const readUrlPrefix = @json(url('/notifications'));

                const headers = {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                };

                function formatTime(value) {
                    if (!value) return '';
                    return new Intl.DateTimeFormat('vi-VN', {
                        day: '2-digit',
                        month: '2-digit',
                        year: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                    }).format(new Date(value));
                }

                function renderEmpty(message) {
                    list.replaceChildren();
                    const empty = document.createElement('div');
                    empty.className = 'admin-notification__empty';
                    empty.textContent = message;
                    list.appendChild(empty);
                }

                function renderNotifications(items) {
                    if (!items || !items.length) {
                        renderEmpty('Chưa có thông báo nào.');
                        return;
                    }

                    list.replaceChildren();

                    items.forEach((notification) => {
                        const item = document.createElement('a');
                        item.href = notification.link || '#';
                        item.className = `admin-notification__item ${notification.is_read ? '' : 'is-unread'}`;

                        const title = document.createElement('p');
                        title.className = 'admin-notification__item-title';
                        title.textContent = notification.title || 'Thông báo';

                        const content = document.createElement('p');
                        content.className = 'admin-notification__item-content';
                        content.textContent = notification.content || '';

                        const time = document.createElement('p');
                        time.className = 'admin-notification__item-time';
                        time.textContent = formatTime(notification.created_at);

                        item.append(title, content, time);
                        item.addEventListener('click', async (event) => {
                            if (!notification.link) {
                                event.preventDefault();
                            }

                            if (!notification.is_read) {
                                try {
                                    await fetch(`${readUrlPrefix}/${notification.id}/read`, {
                                        method: 'POST',
                                        headers,
                                    });
                                } catch (error) {
                                    // Ignore fetch error, still allow navigation
                                }
                            }
                        });

                        list.appendChild(item);
                    });
                }

                async function loadUnreadCount() {
                    try {
                        const response = await fetch(unreadCountUrl, { headers: { 'Accept': 'application/json' } });
                        if (!response.ok) throw new Error();
                        const data = await response.json();
                        const count = Number(data.count || 0);
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.style.display = count > 0 ? 'inline-block' : 'none';
                    } catch (error) {
                        badge.style.display = 'none';
                    }
                }

                async function loadNotifications() {
                    renderEmpty('Đang tải...');
                    try {
                        const response = await fetch(latestUrl, { headers: { 'Accept': 'application/json' } });
                        if (!response.ok) throw new Error();
                        renderNotifications(await response.json());
                    } catch (error) {
                        renderEmpty('Không thể tải thông báo.');
                    }
                }

                button.addEventListener('click', async (event) => {
                    event.stopPropagation();
                    const isOpening = dropdown.hasAttribute('hidden');
                    dropdown.toggleAttribute('hidden');

                    if (isOpening) {
                        await Promise.all([loadNotifications(), loadUnreadCount()]);
                    }
                });

                document.addEventListener('click', (event) => {
                    if (!root.contains(event.target)) {
                        dropdown.setAttribute('hidden', '');
                    }
                });

                readAllButton?.addEventListener('click', async () => {
                    try {
                        const response = await fetch(markAllReadUrl, {
                            method: 'POST',
                            headers,
                        });
                        if (!response.ok) throw new Error();
                        await Promise.all([loadNotifications(), loadUnreadCount()]);
                    } catch (error) {
                        renderEmpty('Không thể đánh dấu đã đọc.');
                    }
                });

                loadUnreadCount();
            });
        </script>
    @endauth
</body>
</html>
