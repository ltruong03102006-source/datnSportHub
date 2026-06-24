function qs(selector, ctx = document) { return ctx.querySelector(selector); }

async function fetchUnreadCount() {
    try {
        const res = await fetch('/notifications/unread-count', { credentials: 'same-origin' });
        if (!res.ok) return;
        const data = await res.json();
        const badge = document.getElementById('notification-badge');
        if (!badge) return;
        const count = data.count || 0;
        if (count <= 0) {
            badge.style.display = 'none';
        } else {
            badge.style.display = 'inline-flex';
            badge.textContent = count > 99 ? '99+' : String(count);
        }
    } catch (e) {
        // ignore
    }
}

async function fetchLatestAndRender() {
    try {
        const res = await fetch('/notifications/latest', { credentials: 'same-origin' });
        if (!res.ok) return;
        const items = await res.json();
        const container = document.getElementById('notification-list');
        if (!container) return;
        if (!items || items.length === 0) {
            container.innerHTML = '<div class="p-4 text-sm text-zinc-500">Không có thông báo</div>';
            return;
        }

        container.innerHTML = items.map(n => {
            const time = new Date(n.created_at).toLocaleString();
            const readClass = n.is_read ? 'bg-stone-50 text-zinc-500' : 'bg-white text-zinc-900 font-semibold';
            const link = n.link ? `href="${n.link}"` : 'href="#" onclick="return false;"';
            return `
                <a ${link} data-id="${n.id}" class="block px-4 py-3 border-b border-stone-100 ${readClass} notification-item">
                    <div class="text-sm">${n.title}</div>
                    <div class="mt-1 text-xs text-zinc-500">${n.content}</div>
                    <div class="mt-1 text-[11px] text-zinc-400">${time}</div>
                </a>
            `;
        }).join('\n');

        // attach click to mark as read
        container.querySelectorAll('.notification-item').forEach(el => {
            el.addEventListener('click', async (e) => {
                const id = el.getAttribute('data-id');
                try {
                    await fetch(`/notifications/${id}/read`, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    });
                    // optimistic update
                    el.classList.remove('font-semibold');
                    fetchUnreadCount();
                } catch (err) {}
            });
        });

    } catch (e) {
        // ignore
    }
}

document.addEventListener('DOMContentLoaded', () => {
    fetchUnreadCount();
    setInterval(fetchUnreadCount, 30000);

    const btn = document.getElementById('notification-button');
    if (btn) {
        btn.addEventListener('click', () => {
            // small delay to allow dropdown to show
            setTimeout(fetchLatestAndRender, 120);
        });
    }

    const markAllBtn = document.getElementById('mark-all-read');
    if (markAllBtn) {
        markAllBtn.addEventListener('click', async () => {
            try {
                await fetch('/notifications/read-all', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });
                fetchUnreadCount();
                fetchLatestAndRender();
            } catch (e) {}
        });
    }
});

export {};
