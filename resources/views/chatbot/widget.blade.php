@if(config('chatbot.enabled'))
<div id="sporthub-chatbot" class="fixed bottom-5 right-5 z-50 font-sans">
    <button type="button" id="chatbot-toggle" class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-600 text-white shadow-xl transition hover:bg-emerald-700" aria-label="Mở chatbot">
        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm3.75 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 0 1-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8Z" />
        </svg>
    </button>

    <div id="chatbot-panel" class="hidden w-[min(24rem,calc(100vw-2rem))] overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-2xl">
        <div class="flex items-center justify-between bg-gradient-to-r from-emerald-600 to-teal-500 px-4 py-3 text-white">
            <div>
                <p class="text-sm font-extrabold">{{ config('chatbot.name', 'SportHub Bot') }}</p>
                <p class="text-xs text-emerald-50">Hỗ trợ nhanh về đặt sân</p>
            </div>
            <button type="button" id="chatbot-close" class="rounded-full p-1 text-white/90 hover:bg-white/15" aria-label="Đóng chatbot">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div id="chatbot-messages" class="max-h-96 min-h-72 space-y-3 overflow-y-auto bg-stone-50 p-4 text-sm">
            <div class="max-w-[85%] rounded-2xl rounded-bl-md bg-white px-3 py-2 text-slate-700 shadow-sm">
                Xin chào, mình có thể hỗ trợ bạn về đặt sân, thanh toán, hủy lịch, đổi lịch và đánh giá.
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="chatbot-suggestion rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700" data-message="Tôi muốn đặt sân">Đặt sân</button>
                <button type="button" class="chatbot-suggestion rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700" data-message="Hướng dẫn thanh toán">Thanh toán</button>
                <button type="button" class="chatbot-suggestion rounded-full bg-amber-50 px-3 py-1.5 text-xs font-bold text-amber-700" data-message="Tôi muốn đổi lịch">Đổi lịch</button>
            </div>
        </div>

        <form id="chatbot-form" class="flex gap-2 border-t border-stone-200 bg-white p-3">
            <input id="chatbot-input" type="text" maxlength="{{ config('chatbot.max_user_message_length', 1000) }}" autocomplete="off" class="min-w-0 flex-1 rounded-xl border border-stone-300 px-3 py-2 text-sm outline-none focus:border-emerald-500" placeholder="Nhập câu hỏi...">
            <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white hover:bg-emerald-700">
                Gửi
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('chatbot-toggle');
    const close = document.getElementById('chatbot-close');
    const panel = document.getElementById('chatbot-panel');
    const form = document.getElementById('chatbot-form');
    const input = document.getElementById('chatbot-input');
    const messages = document.getElementById('chatbot-messages');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    let conversationId = Number(localStorage.getItem('sporthub_chatbot_conversation_id')) || null;

    const appendMessage = (text, sender = 'bot') => {
        const item = document.createElement('div');
        item.className = sender === 'user'
            ? 'ml-auto max-w-[85%] rounded-2xl rounded-br-md bg-emerald-600 px-3 py-2 text-white shadow-sm'
            : 'max-w-[85%] rounded-2xl rounded-bl-md bg-white px-3 py-2 text-slate-700 shadow-sm';
        item.textContent = text;
        messages.appendChild(item);
        messages.scrollTop = messages.scrollHeight;
    };

    toggle?.addEventListener('click', () => {
        panel.classList.toggle('hidden');
        if (!panel.classList.contains('hidden')) input?.focus();
    });

    close?.addEventListener('click', () => panel.classList.add('hidden'));

    const sendMessage = async (text) => {
        if (!text) return;

        appendMessage(text, 'user');
        input.value = '';
        input.disabled = true;

        try {
            const response = await fetch(@json(route('chatbot.message')), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({ message: text, conversation_id: conversationId }),
            });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error('Chatbot error');

            conversationId = data.conversation_id;
            localStorage.setItem('sporthub_chatbot_conversation_id', conversationId);
            appendMessage(data.message.message, 'bot');
        } catch (error) {
            appendMessage('Hiện tại chatbot chưa phản hồi được. Bạn thử lại sau nhé.', 'bot');
        } finally {
            input.disabled = false;
            input.focus();
        }
    };

    document.querySelectorAll('.chatbot-suggestion').forEach((button) => {
        button.addEventListener('click', () => sendMessage(button.dataset.message || button.textContent.trim()));
    });

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const text = input.value.trim();
        await sendMessage(text);
    });
});
</script>
@endif
