window.QLevoChat = (function () {
    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('input[name="_token"]')?.value;
    }

    function scrollToBottom(el) { el.scrollTop = el.scrollHeight; }

    function appendUserBubble(threadEl, text) {
        const div = document.createElement('div');
        div.className = 'concierge-bubble user';
        div.innerHTML = `<div class="concierge-bubble-body"></div>`;
        div.querySelector('.concierge-bubble-body').textContent = text;
        threadEl.appendChild(div);
        scrollToBottom(threadEl);
    }

    function appendThinking(threadEl) {
        const div = document.createElement('div');
        div.className = 'concierge-bubble ai';
        div.id = 'concierge-thinking-row';
        div.innerHTML = `<div class="concierge-avatar"></div><div class="concierge-thinking"><span></span><span></span><span></span></div>`;
        threadEl.appendChild(div);
        scrollToBottom(threadEl);
    }

    function removeThinking(threadEl) {
        threadEl.querySelector('#concierge-thinking-row')?.remove();
    }

    function appendAiBubble(threadEl, text) {
        const div = document.createElement('div');
        div.className = 'concierge-bubble ai';
        div.innerHTML = `<div class="concierge-avatar"></div><div class="concierge-bubble-body"></div>`;
        div.querySelector('.concierge-bubble-body').textContent = text;
        threadEl.appendChild(div);
        scrollToBottom(threadEl);
    }

    function initConcierge({ sendUrl, threadEl, formEl, inputEl, statusEl }) {
        const empty = threadEl.querySelector('.concierge-empty');

        threadEl.querySelectorAll('.concierge-chip').forEach((chip) => {
            chip.addEventListener('click', () => {
                inputEl.value = chip.dataset.fill || chip.textContent.trim();
                formEl.requestSubmit();
            });
        });

        formEl.addEventListener('submit', async (e) => {
            e.preventDefault();
            const text = inputEl.value.trim();
            if (!text) return;

            if (empty) empty.remove();
            appendUserBubble(threadEl, text);
            inputEl.value = '';
            appendThinking(threadEl);
            if (statusEl) statusEl.textContent = 'thinking…';

            try {
                const res = await fetch(sendUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: text }),
                });

                removeThinking(threadEl);

                if (!res.ok) {
                    const err = await res.json().catch(() => ({}));
                    appendAiBubble(threadEl, err.error || "Sorry, I'm having trouble right now.");
                    return;
                }

                const data = await res.json();
                appendAiBubble(threadEl, data.reply || 'Done.');

                (data.client_actions || []).forEach((action) => {
                    if (action.type === 'navigate' && action.url) {
                        setTimeout(() => { window.location.href = action.url; }, 600);
                    }
                });
            } catch (err) {
                removeThinking(threadEl);
                appendAiBubble(threadEl, "Sorry, I'm having trouble right now.");
            } finally {
                if (statusEl) statusEl.textContent = 'always here';
            }
        });
    }

    return { initConcierge };
})();
