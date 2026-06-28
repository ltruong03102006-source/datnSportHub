@auth
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const root = document.getElementById('owner-notification-root');
            const button = document.getElementById('owner-notification-button');
            const dropdown = document.getElementById('owner-notification-dropdown');
            const list = document.getElementById('owner-notification-list');
            const badge = document.getElementById('owner-notification-badge');
            const readAllButton = document.getElementById('owner-notification-read-all');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

            if (!root || !button || !dropdown || !list || !badge) {
                return;
            }

            const latestUrl = @json(route('notifications.latest', ['context' => 'owner']));
            const unreadCountUrl = @json(route('notifications.unread-count', ['context' => 'owner']));
            const markAllReadUrl = @json(route('notifications.read-all', ['context' => 'owner']));
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
                empty.className = 'owner-notification__empty';
                empty.textContent = message;
                list.appendChild(empty);
            }

            function renderNotifications(items) {
                if (!items.length) {
                    renderEmpty('Bạn chưa có thông báo nào.');
                    return;
                }

                list.replaceChildren();

                items.forEach((notification) => {
                    const item = document.createElement('a');
                    item.href = notification.link || '#';
                    item.className = `owner-notification__item ${notification.is_read ? '' : 'is-unread'}`;

                    const title = document.createElement('p');
                    title.className = 'owner-notification__item-title';
                    title.textContent = notification.title || 'Thông báo';

                    const content = document.createElement('p');
                    content.className = 'owner-notification__item-content';
                    content.textContent = notification.content || '';

                    const time = document.createElement('p');
                    time.className = 'owner-notification__item-time';
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
                                // Không chặn mở link nếu request đánh dấu đã đọc lỗi.
                            }
                        }
                    });

                    list.appendChild(item);
                });
            }

            async function loadUnreadCount() {
                try {
                    const response = await fetch(unreadCountUrl, { headers: { 'Accept': 'application/json' } });
                    if (!response.ok) throw new Error('Không tải được số thông báo.');
                    const data = await response.json();
                    const count = Number(data.count || 0);
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.style.display = count > 0 ? 'block' : 'none';
                } catch (error) {
                    badge.style.display = 'none';
                }
            }

            async function loadNotifications() {
                renderEmpty('Đang tải...');

                try {
                    const response = await fetch(latestUrl, { headers: { 'Accept': 'application/json' } });
                    if (!response.ok) throw new Error('Không tải được thông báo.');
                    renderNotifications(await response.json());
                } catch (error) {
                    renderEmpty('Không thể tải thông báo. Vui lòng thử lại.');
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
                    if (!response.ok) throw new Error('Không đánh dấu được thông báo.');
                    await Promise.all([loadNotifications(), loadUnreadCount()]);
                } catch (error) {
                    renderEmpty('Không thể đánh dấu đã đọc. Vui lòng thử lại.');
                }
            });

            loadUnreadCount();
        });
    </script>
@endauth
