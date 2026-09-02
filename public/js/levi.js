document.addEventListener('DOMContentLoaded', function () {
    function initWidget(widget) {
        const endpoint = widget.dataset.endpoint;
        const messages = widget.querySelector('.levi-messages');
        const form = widget.querySelector('.levi-form');
        const input = widget.querySelector('.levi-input');
        if (!endpoint || !messages || !form || !input) return;

        function appendMessage(text, who) {
            const el = document.createElement('div');
            el.className = 'levi-msg levi-msg-' + who;
            el.textContent = text;
            messages.appendChild(el);
            messages.scrollTop = messages.scrollHeight;
        }

        form.addEventListener('submit', async function (e) {
            e.preventDefault();
            const text = input.value.trim();
            if (!text) return;
            appendMessage(text, 'user');
            input.value = '';

            const thinking = document.createElement('div');
            thinking.className = 'levi-msg levi-msg-ai';
            thinking.textContent = '…';
            messages.appendChild(thinking);
            messages.scrollTop = messages.scrollHeight;

            try {
                const res = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    },
                    body: JSON.stringify({ message: text }),
                });
                const data = await res.json();
                thinking.remove();

                if (data.error) {
                    appendMessage("Sorry, I hit a snag - try again in a moment.", 'ai');
                } else {
                    appendMessage(data.reply, 'ai');
                    (data.client_actions || []).forEach(action => {
                        if (action.type === 'navigate') {
                            setTimeout(() => { window.location.href = action.url; }, 900);
                        }
                    });
                }
            } catch (err) {
                thinking.remove();
                appendMessage("Sorry, I hit a snag - try again in a moment.", 'ai');
            }
        });
    }

    document.querySelectorAll('.levi-chat-widget').forEach(initWidget);

    // Bubble open/close (only present on non-dashboard pages)
    const bubble = document.getElementById('leviBubble');
    const panel = document.getElementById('leviPanel');
    if (bubble && panel) {
        const backdrop = document.getElementById('leviBackdrop');
        const closeBtn = document.getElementById('leviClose');
        bubble.addEventListener('click', () => { panel.hidden = false; panel.querySelector('.levi-input')?.focus(); });
        closeBtn.addEventListener('click', () => { panel.hidden = true; });
        backdrop.addEventListener('click', () => { panel.hidden = true; });
    }
});
