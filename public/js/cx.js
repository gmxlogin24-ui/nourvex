(function () {
  function g(i) { return document.getElementById(i); }
  function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]; }); }
  function lin(s) { return esc(s).replace(/(https?:\/\/[^\s<]+)/g, '<a href="$1" target="_blank" rel="noopener">$1</a>').replace(/\n/g, '<br>'); }
  function api(R, url, opt) {
    opt = opt || {};
    opt.headers = { 'X-CSRF-TOKEN': R.dataset.csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' };
    opt.credentials = 'same-origin';
    return fetch(url, opt).then(function (r) { return r.json().catch(function () { return {}; }); });
  }
  function form(o) { var fd = new FormData(); Object.keys(o).forEach(function (k) { if (o[k] != null) fd.append(k, o[k]); }); return fd; }
  function icons() { if (window.lucide) lucide.createIcons(); }
  function toast(t) { var d = document.createElement('div'); d.className = 'cx-toast'; d.textContent = t; document.body.appendChild(d); setTimeout(function () { d.remove(); }, 2200); }
  var SEND = '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7z"/></svg>';
  var MIC = '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="2" width="6" height="12" rx="3"/><path d="M5 10a7 7 0 0 0 14 0M12 19v3"/></svg>';

  var T = g('cxT'), I = g('cxI');
  if (I) inbox(I);
  if (T) thread(T);

  function inbox(R) {
    var Q = g('cxQ'), list = g('cxList'), base = R.dataset.base, NS = g('cxNS'), NQ = g('cxNQ'), NR = g('cxNR'), tm;
    function filt() { var v = Q.value.trim().toLowerCase(); [].forEach.call(list.querySelectorAll('.cx-row'), function (r) { r.style.display = (!v || r.dataset.name.indexOf(v) > -1) ? '' : 'none'; }); }
    Q.addEventListener('input', filt);
    setInterval(function () {
      if (document.hidden) return;
      fetch(location.href, { credentials: 'same-origin' }).then(function (r) { return r.text(); }).then(function (h) {
        var d = new DOMParser().parseFromString(h, 'text/html').getElementById('cxList');
        if (d && d.innerHTML !== list.innerHTML) { list.innerHTML = d.innerHTML; icons(); filt(); }
      });
    }, 6000);
    function people() {
      api(R, base + '/people?q=' + encodeURIComponent(NQ.value.trim())).then(function (a) {
        NR.innerHTML = (a || []).map(function (u) {
          return '<button type="button" class="cx-row" data-u="' + u.id + '"><span class="cx-av">' + (u.avatar ? '<img src="' + esc(u.avatar) + '" alt="">' : esc((u.name || '?').charAt(0).toUpperCase())) + '</span><span class="cx-mid"><b>' + esc(u.name) + '</b><small>' + esc(u.sub) + '</small></span></button>';
        }).join('') || '<p class="cx-empty">No one found</p>';
      });
    }
    g('cxNew').addEventListener('click', function () { NS.hidden = false; NQ.value = ''; people(); NQ.focus(); });
    NS.querySelector('.cx-bk').addEventListener('click', function () { NS.hidden = true; });
    NQ.addEventListener('input', function () { clearTimeout(tm); tm = setTimeout(people, 250); });
    NR.addEventListener('click', function (e) {
      var b = e.target.closest('[data-u]'); if (!b) return;
      api(R, base + '/start', { method: 'POST', body: form({ user_id: b.dataset.u }) }).then(function (d) { if (d && d.url) location.href = d.url; else toast('Could not open chat'); });
    });
  }

  function thread(R) {
    var id = R.dataset.id, root = R.dataset.base, base = root + '/' + id, grp = R.dataset.group === '1';
    var L = g('cxL'), TX = g('cxTx'), GO = g('cxGo'), ST = g('cxSt'), SH = g('cxSh'), BAR = g('cxBar'), BT = g('cxBarT'), IN = g('cxIn'), REC = g('cxRec');
    var sig = '', first = true, reply = null, edit = null, qs = '', lastTy = 0, rec = null, map = {}, stick = true;

    function fit() { TX.style.height = 'auto'; TX.style.height = Math.min(TX.scrollHeight, 120) + 'px'; }
    function mode() { var s = TX.value.trim() || edit; GO.innerHTML = s ? SEND : MIC; GO.dataset.m = s ? 's' : 'm'; }
    mode();

    function size(n) { n = +n || 0; return n > 1048576 ? (n / 1048576).toFixed(1) + ' MB' : Math.max(1, Math.round(n / 1024)) + ' KB'; }
    function bubble(m) {
      var c = '';
      if (m.deleted) c = '<i class="dl">🚫 This message was deleted</i>';
      else {
        if (m.reply) c += '<div class="rq"><b>' + esc(m.reply.name) + '</b><span>' + esc(m.reply.text) + '</span></div>';
        if (m.type === 'image' && m.meta) c += '<img class="im" src="' + esc(m.meta.url) + '" alt="">';
        else if (m.type === 'voice' && m.meta) c += '<audio controls preload="none" src="' + esc(m.meta.url) + '"></audio>';
        else if (m.type === 'file' && m.meta) c += '<a class="fl" href="' + esc(m.meta.url) + '" target="_blank" download>📎<span>' + esc(m.meta.name) + '</span><small>' + size(m.meta.size) + '</small></a>';
        if (m.body) c += '<div class="tx">' + lin(m.body) + '</div>';
      }
      var tk = (m.mine && !m.deleted) ? '<span class="tk ' + m.status + '">' + (m.status === 'sent' ? '✓' : '✓✓') + '</span>' : '';
      var rx = (m.rx || []).map(function (r) { return '<span class="' + (r.me ? 'me' : '') + '">' + r.e + (r.n > 1 ? ' ' + r.n : '') + '</span>'; }).join('');
      return '<div class="m ' + (m.mine ? 'me' : 'th') + '" data-id="' + m.id + '"><div class="b">' + (grp && !m.mine ? '<div class="sn">' + esc(m.sender) + '</div>' : '') + c + '<div class="t">' + ((m.edited && !m.deleted) ? 'edited · ' : '') + esc(m.time) + tk + '</div>' + (rx ? '<div class="rx">' + rx + '</div>' : '') + '</div></div>';
    }

    function draw(d) {
      var s = JSON.stringify([d.messages, d.typing, d.peer.status]);
      if (s === sig) return; sig = s;
      ST.textContent = d.typing ? 'typing…' : (d.peer.status || '');
      ST.className = (d.typing || d.peer.online) ? 'on' : '';
      var st = L.scrollTop, near = L.scrollHeight - L.scrollTop - L.clientHeight < 140, html = '', day = '';
      map = {};
      d.messages.forEach(function (m) { map[m.id] = m; if (m.day !== day) { day = m.day; html += '<div class="dy"><span>' + esc(day) + '</span></div>'; } html += bubble(m); });
      if (!d.messages.length) html = '<div class="em">' + (qs ? 'No matches' : 'Say hello 👋') + '</div>';
      L.innerHTML = html;
      stick = first || (near && !qs);
      L.scrollTop = stick ? L.scrollHeight : st;
      first = false;
    }
    L.addEventListener('load', function () { if (stick) L.scrollTop = L.scrollHeight; }, true);

    function poll() { if (document.hidden) return; api(R, base + '/messages?q=' + encodeURIComponent(qs)).then(function (d) { if (d && d.messages) draw(d); }); }
    poll(); setInterval(poll, 2000); document.addEventListener('visibilitychange', poll);

    function ctx() {
      if (edit) { BAR.hidden = false; BT.textContent = 'Editing message'; }
      else if (reply) { BAR.hidden = false; BT.textContent = reply.sender + ': ' + (reply.body || reply.type); }
      else BAR.hidden = true;
      mode();
    }
    g('cxBarX').addEventListener('click', function () { if (edit) TX.value = ''; reply = null; edit = null; fit(); ctx(); });

    function send(o) {
      o = o || {};
      var t = TX.value.trim();
      if (edit) {
        if (!t) return;
        var e = edit; edit = null; TX.value = ''; fit(); ctx();
        api(R, root + '/msg/' + e.id + '/edit', { method: 'POST', body: form({ body: t }) }).then(function () { sig = ''; poll(); });
        return;
      }
      if (!t && !o.file) return;
      var fd = form({ body: t, reply_to: reply ? reply.id : null, kind: o.kind, dur: o.dur });
      if (o.file) fd.append('file', o.file, o.name || 'file');
      TX.value = ''; fit(); reply = null; ctx();
      if (o.file) toast('Sending…');
      api(R, base + '/send', { method: 'POST', body: fd }).then(function (d) { if (d && d.ok) { sig = ''; stick = true; first = true; poll(); } else toast('Could not send'); });
    }
    GO.addEventListener('click', function () { if (GO.dataset.m === 's') send(); else startRec(); });
    TX.addEventListener('input', function () {
      fit(); mode();
      if (Date.now() - lastTy > 2500) { lastTy = Date.now(); api(R, base + '/typing', { method: 'POST', body: form({}) }); }
    });
    TX.addEventListener('keydown', function (e) { if (e.key === 'Enter' && !e.shiftKey && window.matchMedia('(pointer:fine)').matches) { e.preventDefault(); send(); } });
    g('cxF').addEventListener('change', function () { var f = this.files[0]; this.value = ''; if (f) send({ file: f, name: f.name }); });

    function startRec() {
      if (!navigator.mediaDevices || !window.MediaRecorder) { toast('Voice notes need a secure (https) connection'); return; }
      navigator.mediaDevices.getUserMedia({ audio: true }).then(function (s) {
        var ch = [], mt = ['audio/webm;codecs=opus', 'audio/mp4', 'audio/webm'].filter(function (t) { return MediaRecorder.isTypeSupported(t); })[0];
        var mr = new MediaRecorder(s, mt ? { mimeType: mt } : {});
        rec = { mr: mr, t0: Date.now(), cancel: false };
        mr.ondataavailable = function (e) { if (e.data && e.data.size) ch.push(e.data); };
        mr.onstop = function () {
          s.getTracks().forEach(function (t) { t.stop(); });
          clearInterval(rec.iv); REC.hidden = true; IN.hidden = false;
          var r = rec; rec = null;
          if (!r.cancel && ch.length) {
            var type = mr.mimeType || 'audio/webm';
            send({ file: new Blob(ch, { type: type }), name: 'voice.' + (type.indexOf('mp4') > -1 ? 'm4a' : 'webm'), kind: 'voice', dur: Math.round((Date.now() - r.t0) / 1000) });
          }
        };
        mr.start(); IN.hidden = true; REC.hidden = false;
        rec.iv = setInterval(function () { var s2 = Math.floor((Date.now() - rec.t0) / 1000); g('cxRT').textContent = Math.floor(s2 / 60) + ':' + ('0' + s2 % 60).slice(-2); }, 500);
      }).catch(function () { toast('Microphone blocked'); });
    }
    g('cxRS').addEventListener('click', function () { if (rec) rec.mr.stop(); });
    g('cxRX').addEventListener('click', function () { if (rec) { rec.cancel = true; rec.mr.stop(); } });

    L.addEventListener('click', function (e) {
      if (e.target.closest('a,audio')) return;
      var im = e.target.closest('.im');
      if (im) { var lb = document.createElement('div'); lb.className = 'cx-lb'; lb.innerHTML = '<img src="' + esc(im.src) + '" alt="">'; lb.onclick = function () { lb.remove(); }; document.body.appendChild(lb); return; }
      var b = e.target.closest('.m'); if (!b) return;
      var m = map[b.dataset.id]; if (!m || m.deleted) return;
      var h = '<div class="cx-bk"></div><div class="cx-pn"><div class="cx-em">' + ['👍', '❤️', '😂', '😮', '😢', '🙏'].map(function (x) { return '<button type="button" data-e="' + x + '">' + x + '</button>'; }).join('') + '</div>';
      h += '<button type="button" class="cx-ac" data-a="reply">↩️ Reply</button>';
      if (m.body) h += '<button type="button" class="cx-ac" data-a="copy">📋 Copy</button>';
      if (m.mine && m.type === 'text') h += '<button type="button" class="cx-ac" data-a="edit">✏️ Edit</button>';
      if (m.mine) h += '<button type="button" class="cx-ac red" data-a="del">🗑️ Delete for everyone</button>';
      SH.innerHTML = h + '</div>'; SH.hidden = false; SH.dataset.id = m.id;
    });
    SH.addEventListener('click', function (e) {
      if (e.target.classList.contains('cx-bk')) { SH.hidden = true; return; }
      var m = map[SH.dataset.id]; if (!m) return;
      var em = e.target.closest('[data-e]'), a = e.target.closest('[data-a]');
      if (em) { SH.hidden = true; api(R, root + '/msg/' + m.id + '/react', { method: 'POST', body: form({ emoji: em.dataset.e }) }).then(function () { sig = ''; poll(); }); }
      else if (a) {
        SH.hidden = true; var k = a.dataset.a;
        if (k === 'reply') { reply = m; edit = null; ctx(); TX.focus(); }
        else if (k === 'copy') { if (navigator.clipboard) navigator.clipboard.writeText(m.body).then(function () { toast('Copied'); }); }
        else if (k === 'edit') { edit = m; reply = null; TX.value = m.body; fit(); ctx(); TX.focus(); }
        else if (k === 'del') { if (confirm('Delete this message for everyone?')) api(R, root + '/msg/' + m.id + '/delete', { method: 'POST', body: form({}) }).then(function () { sig = ''; poll(); }); }
      }
    });

    var SR = g('cxSR'), SI = g('cxSI'), tm;
    g('cxSB').addEventListener('click', function () { SR.hidden = !SR.hidden; if (!SR.hidden) SI.focus(); else { SI.value = ''; qs = ''; sig = ''; first = true; poll(); } });
    SI.addEventListener('input', function () { clearTimeout(tm); tm = setTimeout(function () { qs = SI.value.trim(); sig = ''; poll(); }, 300); });
  }
})();
