@auth
    <style>
        .owner-notification {
            position: relative;
        }

        .owner-notification__button {
            width: 42px;
            height: 42px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            color: #475569;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all .2s ease;
        }

        .owner-notification__button:hover {
            border-color: #10b981;
            color: #059669;
            background: #ecfdf5;
        }

        .owner-notification__badge {
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 19px;
            height: 19px;
            padding: 0 5px;
            border-radius: 999px;
            background: #ef4444;
            color: #ffffff;
            font-size: 10px;
            font-weight: 800;
            line-height: 19px;
            text-align: center;
            display: none;
        }

        .owner-notification__dropdown {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: min(380px, calc(100vw - 28px));
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 22px 45px rgba(15, 23, 42, .16);
            overflow: hidden;
            z-index: 1000;
        }

        .owner-notification__dropdown[hidden] {
            display: none;
        }

        .owner-notification__head,
        .owner-notification__foot {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 12px 14px;
            border-bottom: 1px solid #f1f5f9;
        }

        .owner-notification__foot {
            border-top: 1px solid #f1f5f9;
            border-bottom: 0;
        }

        .owner-notification__title {
            margin: 0;
            color: #0f172a;
            font-size: 14px;
            font-weight: 800;
        }

        .owner-notification__action {
            border: 0;
            background: transparent;
            color: #059669;
            cursor: pointer;
            font-size: 12px;
            font-weight: 700;
            text-decoration: none;
        }

        .owner-notification__list {
            max-height: 360px;
            overflow-y: auto;
        }

        .owner-notification__item {
            display: block;
            padding: 13px 14px;
            border-bottom: 1px solid #f1f5f9;
            color: inherit;
            text-decoration: none;
            transition: background .2s ease;
        }

        .owner-notification__item:hover {
            background: #f8fafc;
            text-decoration: none;
        }

        .owner-notification__item.is-unread {
            background: #ecfdf5;
        }

        .owner-notification__item-title {
            margin: 0;
            color: #0f172a;
            font-size: 13px;
            font-weight: 800;
        }

        .owner-notification__item-content {
            margin: 4px 0 0;
            color: #64748b;
            font-size: 12px;
            line-height: 1.45;
        }

        .owner-notification__item-time {
            margin: 5px 0 0;
            color: #94a3b8;
            font-size: 11px;
        }

        .owner-notification__empty {
            padding: 18px 14px;
            color: #64748b;
            font-size: 13px;
        }
    </style>

    <div class="owner-notification" id="owner-notification-root">
        <button type="button" class="owner-notification__button" id="owner-notification-button" aria-label="Thông báo">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M15 17H9m10-1.2c-.8-.98-1.5-1.48-1.5-4.3A5.5 5.5 0 0 0 12 6a5.5 5.5 0 0 0-5.5 5.5c0 2.82-.7 3.32-1.5 4.3-.33.4-.04 1.02.48 1.02h13.04c.52 0 .81-.61.48-1.02Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M10 20a2.2 2.2 0 0 0 4 0" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </button>
        <span class="owner-notification__badge" id="owner-notification-badge">0</span>

        <div class="owner-notification__dropdown" id="owner-notification-dropdown" hidden>
            <div class="owner-notification__head">
                <p class="owner-notification__title">Thông báo chủ sân</p>
                <button type="button" class="owner-notification__action" id="owner-notification-read-all">
                    Đánh dấu đã đọc
                </button>
            </div>

            <div class="owner-notification__list" id="owner-notification-list">
                <div class="owner-notification__empty">Đang tải...</div>
            </div>

            <div class="owner-notification__foot">
                <a href="{{ route('notifications.index', ['context' => 'owner']) }}" class="owner-notification__action">Xem tất cả</a>
            </div>
        </div>
    </div>
@endauth
