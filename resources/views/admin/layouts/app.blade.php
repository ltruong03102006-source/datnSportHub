<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sport Booking - Admin Dashboard</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
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

        .notification {
            position: relative;
            color: var(--text-muted);
            font-size: 18px;
            cursor: pointer;
        }
        .notification .badge {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 8px;
            height: 8px;
            background-color: #e74c3c;
            border-radius: 50%;
            border: 2px solid white;
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
            <a href="{{ route('admin.venues.index') }}" class="nav-item {{ request()->routeIs('admin.venues.*') ? 'active' : '' }}">
                <i class="fa-solid fa-building"></i> Quản lý sân
            </a>
            <a href="{{ route('admin.bookings.index') }}" class="nav-item {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
                <i class="fa-regular fa-calendar-check"></i> Quản lý đặt sân
            </a>
            <!-- Mock links matching the screenshot -->
            <a href="#" class="nav-item"><i class="fa-solid fa-wallet"></i> Thanh toán</a>
            <a href="#" class="nav-item"><i class="fa-regular fa-star"></i> Đánh giá</a>
            <a href="#" class="nav-item"><i class="fa-solid fa-triangle-exclamation"></i> Báo cáo vi phạm</a>
            <a href="#" class="nav-item"><i class="fa-regular fa-bell"></i> Thông báo</a>
            <a href="#" class="nav-item"><i class="fa-solid fa-chart-simple"></i> Thống kê</a>
            <a href="#" class="nav-item"><i class="fa-solid fa-gear"></i> Cài đặt hệ thống</a>
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
                <div class="notification">
                    <i class="fa-regular fa-bell"></i>
                    <span class="badge"></span>
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
</body>
</html>
