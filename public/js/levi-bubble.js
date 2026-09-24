(function () {
  function playTone(freq, duration, type) {
    try {
      const ctx = new (window.AudioContext || window.webkitAudioContext)();
      const osc = ctx.createOscillator();
      const gain = ctx.createGain();
      osc.type = type || 'sine';
      osc.frequency.value = freq;
      gain.gain.setValueAtTime(0.08, ctx.currentTime);
      gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
      osc.connect(gain).connect(ctx.destination);
      osc.start();
      osc.stop(ctx.currentTime + duration);
    } catch (e) { /* audio not available, ignore */ }
  }
  function playOpenSound() { playTone(880, 0.12, 'sine'); }
  function playSendSound() { playTone(660, 0.08, 'sine'); }

  function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content;
  }

  const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;

  function speak(text) {
    if (!window.speechSynthesis) return;
    window.speechSynthesis.cancel();
    const utter = new SpeechSynthesisUtterance(text);
    utter.rate = 1.0;
    utter.pitch = 1.0;
    window.speechSynthesis.speak(utter);
  }

  function wireMic(widget) {
    const micBtn = widget.querySelector('.levi-mic-btn');
    const input = widget.querySelector('.levi-input');
    const form = widget.querySelector('.levi-form');
    if (!micBtn || !input || !form) return;

    if (!SpeechRec) {
      micBtn.style.display = 'none'; // not supported in this browser (e.g. Firefox) - hide rather than show a dead button
      return;
    }

    const recognition = new SpeechRec();
    recognition.lang = 'en-US';
    recognition.interimResults = false;
    recognition.maxAlternatives = 1;
    let listening = false;

    micBtn.addEventListener('click', () => {
      if (listening) { recognition.stop(); return; }
      try {
        recognition.start();
        listening = true;
        micBtn.classList.add('levi-mic-active');
      } catch (e) { /* already started, ignore */ }
    });

    recognition.addEventListener('result', (e) => {
      const text = e.results[0][0].transcript;
      input.value = text;
      form.requestSubmit();
    });

    recognition.addEventListener('end', () => {
      listening = false;
      micBtn.classList.remove('levi-mic-active');
    });

    recognition.addEventListener('error', () => {
      listening = false;
      micBtn.classList.remove('levi-mic-active');
    });
  }

  function wireWidget(widget) {
    wireMic(widget);
    widget.querySelectorAll('.levi-chip').forEach((chip) => {
      chip.addEventListener('click', () => {
        const input = widget.querySelector('.levi-input');
        const form = widget.querySelector('.levi-form');
        if (input && form) { input.value = chip.dataset.fill || chip.textContent.trim(); form.requestSubmit(); }
      });
    });
    const endpoint = widget.dataset.endpoint;
    const messages = widget.querySelector('.levi-messages');
    const form = widget.querySelector('.levi-form');
    const input = widget.querySelector('.levi-input');
    if (!endpoint || !messages || !form || !input) return;

    function addMsg(text, cls) {
      const div = document.createElement('div');
      div.className = 'levi-msg ' + cls;
      div.textContent = text;
      messages.appendChild(div);
      messages.scrollTop = messages.scrollHeight;
    }
    function addThinking() {
      const div = document.createElement('div');
      div.className = 'levi-msg-thinking';
      div.dataset.thinking = '1';
      div.innerHTML = '<span></span><span></span><span></span>';
      messages.appendChild(div);
      messages.scrollTop = messages.scrollHeight;
    }
    function removeThinking() {
      messages.querySelector('[data-thinking="1"]')?.remove();
    }

    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      const text = input.value.trim();
      if (!text) return;
      addMsg(text, 'levi-msg-user');
      input.value = '';
      playSendSound();
      addThinking();

      try {
        const res = await fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'Accept': 'application/json',
          },
          body: JSON.stringify({ message: text }),
        });
        removeThinking();

        if (!res.ok) {
          const err = await res.json().catch(() => ({}));
          addMsg(err.error || "Sorry, I'm having trouble right now.", 'levi-msg-ai');
          return;
        }
        const data = await res.json();
        addMsg(data.reply || 'Done.', 'levi-msg-ai');
        speak(data.reply || 'Done.');
        (data.client_actions || []).forEach((action) => {
          if (action.type === 'navigate' && action.url) {
            setTimeout(() => { window.location.href = action.url; }, 500);
          }
        });
      } catch (err) {
        removeThinking();
        addMsg("Sorry, I'm having trouble right now.", 'levi-msg-ai');
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    // Wire every chat widget present on this page (bubble panel, inline card, or both)
    document.querySelectorAll('.levi-chat-widget').forEach(wireWidget);

    // Bubble-specific open/close behavior, only runs if the bubble markup exists
    const bubble = document.getElementById('leviBubble');
    const panel = document.getElementById('leviPanel');
    const backdrop = document.getElementById('leviBackdrop');
    const closeBtn = panel?.querySelector('.levi-close');

    const root = document.getElementById('leviRoot');

    function openPanel() {
      alert('BUBBLE CLICK REACHED HANDLER'); // TEMPORARY DEBUG LINE - remove after testing
      panel.hidden = false;
      root?.classList.add('levi-open');
      playOpenSound();
      // Force a reflow so the browser registers the starting (closed) state
      // before we flip to the open state - otherwise the transition is skipped
      // and it just snaps open instantly instead of animating.
      void panel.offsetWidth;
      panel.classList.add('levi-panel-visible');
      setTimeout(() => panel.querySelector('.levi-input')?.focus(), 380);
    }

    function closePanel() {
      panel.classList.remove('levi-panel-visible');
      root?.classList.remove('levi-open');
      const card = panel.querySelector('.levi-panel-card');
      const onEnd = () => {
        panel.hidden = true;
        card?.removeEventListener('transitionend', onEnd);
      };
      card?.addEventListener('transitionend', onEnd);
      // Fallback in case transitionend doesn't fire (e.g. reduced-motion settings)
      setTimeout(() => { panel.hidden = true; }, 450);
    }

    if (bubble && panel) {
      bubble.addEventListener('click', openPanel);
      closeBtn?.addEventListener('click', closePanel);
      backdrop?.addEventListener('click', closePanel);
    }
  });
})();
