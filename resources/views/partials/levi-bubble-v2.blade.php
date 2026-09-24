@auth
<style>
#lv2-bubble {
  position: fixed !important;
  bottom: 100px !important;
  right: 20px !important;
  width: 58px; height: 58px;
  border-radius: 50%;
  border: none;
  background: transparent;
  cursor: pointer;
  z-index: 2147483647 !important;
  padding: 0;
  overflow: visible;
}
#lv2-bubble .lv2-ring {
  position: absolute; inset: 0; border-radius: 50%;
  border: 1.5px solid rgba(167,139,250,0.5);
  animation: lv2-orbit 4s linear infinite;
}
#lv2-bubble .lv2-ring:nth-child(2) {
  inset: -6px;
  border-color: rgba(109,94,252,0.3);
  animation-duration: 6s;
  animation-direction: reverse;
}
#lv2-bubble .lv2-core {
  position: absolute; inset: 6px; border-radius: 50%;
  background: radial-gradient(circle at 35% 30%, #c4b5fd, #6d5efc 60%, #4c3fd6 100%);
  box-shadow: 0 6px 20px rgba(109,94,252,0.6), inset 0 1px 2px rgba(255,255,255,0.5);
  animation: lv2-core-breathe 2.4s ease-in-out infinite;
  display: flex; align-items: center; justify-content: center;
  color: #fff; font-size: 20px;
}
@keyframes lv2-orbit { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
@keyframes lv2-core-breathe {
  0%, 100% { transform: scale(1); box-shadow: 0 6px 20px rgba(109,94,252,0.5), inset 0 1px 2px rgba(255,255,255,0.5); }
  50% { transform: scale(1.08); box-shadow: 0 8px 28px rgba(109,94,252,0.8), inset 0 1px 2px rgba(255,255,255,0.6); }
}
#lv2-overlay {
  position: fixed !important; inset: 0 !important;
  background: rgba(8,6,16,0.6);
  backdrop-filter: blur(4px);
  z-index: 2147483646 !important;
  display: none;
  align-items: flex-end; justify-content: center;
  opacity: 0;
  transition: opacity 0.25s ease;
}
#lv2-overlay.lv2-visible { display: flex !important; opacity: 1; }
#lv2-overlay {
  padding: 0 12px;
  box-sizing: border-box;
}
#lv2-card {
  width: 100%; max-width: 340px;
  height: min(50vh, 400px);
  margin-bottom: 92px;
  background: linear-gradient(180deg, rgba(40,30,64,0.35), rgba(20,16,36,0.45));
  backdrop-filter: blur(32px) saturate(180%);
  -webkit-backdrop-filter: blur(32px) saturate(180%);
  border: 1px solid rgba(167,139,250,0.3);
  border-radius: 24px;
  display: flex; flex-direction: column;
  overflow: hidden;
  transform-origin: bottom right;
  transform: scale(0.15) translateY(30px);
  opacity: 0;
  transition: transform 0.4s cubic-bezier(0.34, 1.2, 0.64, 1), opacity 0.25s ease;
  box-shadow: 0 20px 60px rgba(0,0,0,0.45), 0 0 90px rgba(109,94,252,0.2), inset 0 1px 1px rgba(255,255,255,0.08);
}
#lv2-overlay.lv2-visible #lv2-card {
  transform: scale(1) translateY(0);
  opacity: 1;
}
#lv2-header { display: flex; justify-content: space-between; align-items: center; padding: 16px 18px; border-bottom: 1px solid rgba(255,255,255,0.08); color: #fff; font-weight: 700; }
#lv2-close { background: rgba(255,255,255,0.08); border: none; color: #fff; width: 28px; height: 28px; border-radius: 50%; font-size: 18px; cursor: pointer; }
#lv2-messages { flex: 1; overflow-y: auto; padding: 16px 18px; display: flex; flex-direction: column; gap: 10px; }
.lv2-msg { max-width: 84%; padding: 10px 14px; border-radius: 16px; font-size: 14px; line-height: 1.45; color: #fff; white-space: pre-wrap; }
.lv2-msg a { color: #c4b5fd; font-weight: 600; text-decoration: underline; }
.lv2-msg-ai { align-self: flex-start; background: rgba(255,255,255,0.08); }
.lv2-msg-user { align-self: flex-end; background: linear-gradient(135deg, #6d5efc, #a78bfa); }
#lv2-form { display: flex; gap: 8px; padding: 12px 16px; border-top: 1px solid rgba(255,255,255,0.08); position: relative; }
#lv2-suggestions {
  position: absolute; bottom: 100%; left: 16px; right: 16px;
  background: rgba(30,24,52,0.92); backdrop-filter: blur(16px);
  border: 1px solid rgba(167,139,250,0.25); border-radius: 12px;
  overflow: hidden; margin-bottom: 6px; display: none;
}
#lv2-suggestions.lv2-visible { display: block; }
.lv2-suggestion {
  padding: 10px 14px; font-size: 13px; color: #e4defc; cursor: pointer;
  border-bottom: 1px solid rgba(255,255,255,0.05);
}
.lv2-suggestion:last-child { border-bottom: none; }
.lv2-suggestion:active, .lv2-suggestion.lv2-active { background: rgba(167,139,250,0.15); }
#lv2-input { flex: 1; background: rgba(255,255,255,0.07); border: 1px solid rgba(255,255,255,0.12); border-radius: 14px; padding: 11px 14px; color: #fff; font-size: 14px; outline: none; }
#lv2-mic, #lv2-send { background: linear-gradient(135deg, #6d5efc, #a78bfa); border: none; border-radius: 14px; width: 42px; color: #fff; font-size: 16px; cursor: pointer; }
#lv2-mic.lv2-listening { background: linear-gradient(135deg, #34d399, #10b981); }
</style>

<button id="lv2-bubble" type="button">
  <span class="lv2-ring"></span>
  <span class="lv2-ring"></span>
  <span class="lv2-core">✦</span>
</button>

<div id="lv2-overlay">
  <div id="lv2-card">
    <div id="lv2-header">
      <span>LEVI</span>
      <button id="lv2-close" type="button">×</button>
    </div>
    <div id="lv2-messages">
      <div class="lv2-msg lv2-msg-ai">Hey{{ auth()->user()->name ? ', ' . explode(' ', auth()->user()->name)[0] : '' }} — what do you need?</div>
    </div>
    <div id="lv2-suggestions"></div>
    <form id="lv2-form">
      <input id="lv2-input" type="text" placeholder="Ask LEVI anything..." autocomplete="off">
      <button id="lv2-mic" type="button"><i class="ph-bold ph-microphone"></i></button>
      <button id="lv2-send" type="submit">→</button>
    </form>
  </div>
</div>

<script>
(function () {
  const endpoint = @json(route('concierge.message'));
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

  const bubble = document.getElementById('lv2-bubble');
  const overlay = document.getElementById('lv2-overlay');
  const closeBtn = document.getElementById('lv2-close');
  const form = document.getElementById('lv2-form');
  const input = document.getElementById('lv2-input');
  const messages = document.getElementById('lv2-messages');
  const micBtn = document.getElementById('lv2-mic');
  const suggestionsBox = document.getElementById('lv2-suggestions');

  const SUGGESTIONS = [
    "Check my wallet balance",
    "Check my Q Coin balance",
    "Claim today's Q Coin reward",
    "Find gigs under 20000",
    "Find gigs under 10000",
    "Show marketplace items under 5000",
    "Post a gig",
    "Create a listing",
    "Take me to my wallet",
  ];

  input.addEventListener('input', function () {
    const val = input.value.trim().toLowerCase();
    if (!val) { suggestionsBox.classList.remove('lv2-visible'); return; }

    const matches = SUGGESTIONS.filter(s => s.toLowerCase().includes(val));
    if (matches.length === 0) { suggestionsBox.classList.remove('lv2-visible'); return; }

    suggestionsBox.innerHTML = matches.slice(0, 4).map(s =>
      '<div class="lv2-suggestion">' + s.replace(/</g, '&lt;') + '</div>'
    ).join('');
    suggestionsBox.classList.add('lv2-visible');

    suggestionsBox.querySelectorAll('.lv2-suggestion').forEach(function (el) {
      el.addEventListener('click', function () {
        input.value = el.textContent;
        suggestionsBox.classList.remove('lv2-visible');
        form.requestSubmit();
      });
    });
  });

  input.addEventListener('blur', function () {
    setTimeout(() => suggestionsBox.classList.remove('lv2-visible'), 150);
  });

  bubble.addEventListener('click', function () {
    bubble.style.opacity = '0';
    bubble.style.transform = 'scale(0.3)';
    bubble.style.transition = 'opacity 0.15s ease, transform 0.15s ease';
    overlay.style.display = 'flex';
    void overlay.offsetWidth;
    overlay.classList.add('lv2-visible');
    setTimeout(() => { bubble.style.display = 'none'; }, 150);
    setTimeout(() => input.focus(), 350);
  });

  function closePanel() {
    overlay.classList.remove('lv2-visible');
    setTimeout(() => {
      overlay.style.display = 'none';
      bubble.style.display = 'flex';
      bubble.style.opacity = '1';
      bubble.style.transform = 'scale(1)';
    }, 300);
  }
  closeBtn.addEventListener('click', closePanel);
  overlay.addEventListener('click', function (e) {
    if (e.target === overlay) closePanel();
  });

  function addMsg(text, cls, html) {
    const div = document.createElement('div');
    div.className = 'lv2-msg ' + cls;
    if (html) { div.innerHTML = html; } else { div.textContent = text; }
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
  }

  function speak(text) {
    if (!window.speechSynthesis) return;
    window.speechSynthesis.cancel();
    window.speechSynthesis.speak(new SpeechSynthesisUtterance(text));
  }

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    const text = input.value.trim();
    if (!text) return;
    addMsg(text, 'lv2-msg-user');
    input.value = '';

    try {
      const res = await fetch(endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        body: JSON.stringify({ message: text }),
      });
      const data = await res.json();
      const reply = data.reply || data.error || 'Done.';
      if (data.links && data.links.length) {
        const linksHtml = data.links.map(l =>
          '<a href="' + l.url + '">' + l.title.replace(/</g, '&lt;') + ' — ' + l.subtitle + '</a>'
        ).join('<br>');
        addMsg(null, 'lv2-msg-ai', reply.replace(/</g, '&lt;') + '<br><br>' + linksHtml);
      } else {
        addMsg(reply, 'lv2-msg-ai');
      }
      speak(reply);
    } catch (err) {
      addMsg("Sorry, I'm having trouble right now.", 'lv2-msg-ai');
    }
  });

  const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (SpeechRec) {
    const recognition = new SpeechRec();
    recognition.lang = 'en-US';
    recognition.interimResults = false;
    let listening = false;

    micBtn.addEventListener('click', function () {
      if (listening) { recognition.stop(); return; }
      try {
        recognition.start();
        listening = true;
        micBtn.classList.add('lv2-listening');
      } catch (e) {
        alert('Mic start failed: ' + e.message);
      }
    });
    recognition.addEventListener('result', function (e) {
      input.value = e.results[0][0].transcript;
      form.requestSubmit();
    });
    recognition.addEventListener('end', function () { listening = false; micBtn.classList.remove('lv2-listening'); });
    recognition.addEventListener('error', function (e) {
      listening = false;
      micBtn.classList.remove('lv2-listening');
      alert('Mic error: ' + e.error); // TEMP DEBUG - shows the real reason (e.g. not-allowed, no-speech, network)
    });
  } else {
    micBtn.style.display = 'none';
  }
})();
</script>
@endauth
