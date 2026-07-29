<style>
    .owner-menu-wrap {
        position: relative;
    }

    .owner-menu-trigger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.45rem;
        border-radius: 999px;
        padding: 0.58rem 0.95rem;
        color: #fff !important;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.2);
        font-size: 0.875rem;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s ease;
        list-style: none;
    }

    .owner-menu-trigger::-webkit-details-marker {
        display: none;
    }

    .owner-menu-trigger:hover {
        background: rgba(255, 255, 255, 0.24);
        transform: translateY(-1px);
    }

    .sporthub-nav .owner-menu-trigger,
    .owner-light-nav .owner-menu-trigger {
        color: #475569 !important;
        background: #f8fafc;
        border-color: #e2e8f0;
    }

    .sporthub-nav .owner-menu-trigger:hover,
    .owner-light-nav .owner-menu-trigger:hover {
        background: #ecfdf5;
        color: #047857 !important;
    }

    .owner-menu-panel {
        position: absolute;
        top: calc(100% + 0.75rem);
        right: 0;
        z-index: 80;
        width: min(18rem, calc(100vw - 2rem));
        padding: 0.5rem;
        border-radius: 1.25rem;
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(226, 232, 240, 0.85);
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.22);
        backdrop-filter: blur(18px) saturate(145%);
        -webkit-backdrop-filter: blur(18px) saturate(145%);
    }

    .owner-menu-panel a {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-radius: 0.9rem;
        padding: 0.78rem 0.9rem;
        color: #334155 !important;
        font-size: 0.9rem;
        font-weight: 700;
        text-decoration: none;
        transition: 0.18s ease;
    }

    .owner-menu-panel a:hover {
        background: #ecfdf5;
        color: #047857 !important;
    }
</style>

<details class="owner-menu-wrap">
    <summary class="owner-menu-trigger">
        Menu
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
        </svg>
    </summary>

    <div class="owner-menu-panel">
        <a href="{{ route('owner.dashboard') }}">Tổng quan</a>
        <a href="{{ route('owner.web.venues.index') }}">Quản lý cơ sở</a>
        <a href="{{ route('owner.web.calendar.index') }}">Lịch đặt sân</a>
        <a href="{{ route('owner.web.checkins.index') }}">Check-in</a>
        <a href="{{ route('owner.web.packages.index') }}">Quản lý gói</a>
        <a href="{{ route('owner.web.reschedule.index') }}">Yêu cầu đổi lịch</a>
        <a href="{{ route('owner.web.settings.bank') }}">Thanh toán (Bank)</a>
    </div>
</details>
