@auth
<link rel="stylesheet" href="{{ asset('css/pulse-extra.css') }}?v={{ filemtime(public_path('css/pulse-extra.css')) }}">
<div class="pf-shade" id="pfShade"></div>
<div class="pf-sheet" id="pfSheet" role="dialog" aria-label="Comments">
    <div class="pf-grip"></div>
    <div class="pf-sh-head"><b id="pfTitle">Comments</b><button type="button" class="pf-x" id="pfClose" aria-label="Close">&times;</button></div>
    <div class="pf-list" id="pfList"></div>
    @auth
    <form class="pf-form" id="pfForm" autocomplete="off">
        <input type="text" id="pfInput" maxlength="300" placeholder="Add a comment...">
        <button type="submit" id="pfSend">Post</button>
    </form>
    @else
    <div class="pf-login"><a href="{{ route('login') }}">Log in</a> to join the conversation</div>
    @endauth
</div>

<script>
(function () {
  var CSRF = '{{ csrf_token() }}', AUTH = {{ auth()->check() ? 'true' : 'false' }};
  var sheet = document.getElementById('pfSheet'), shade = document.getElementById('pfShade');
  var list = document.getElementById('pfList'), title = document.getElementById('pfTitle');
  var form = document.getElementById('pfForm'), input = document.getElementById('pfInput');
  var cur = null, active = null, poll = null, busy = false, replyTo = null;
  document.body.appendChild(shade); document.body.appendChild(sheet);

  var bar = document.createElement('div'); bar.className = 'pf-replying';
  if (form) form.parentNode.insertBefore(bar, form);
  var HEART = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 21s-7.5-4.6-10-9.3C.6 8.1 2.4 4.5 6 4.1c2-.2 3.6.9 6 3.4 2.4-2.5 4-3.6 6-3.4 3.6.4 5.4 4 3 7.6C19.5 16.4 12 21 12 21z"/></svg>';

  var feed = document.getElementById('pulseFeed');
  if (feed && 'IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (es) {
      es.forEach(function (e) { if (e.isIntersecting) active = e.target.getAttribute('data-id'); });
    }, { root: feed, threshold: 0.6 });
    feed.querySelectorAll('.pulse-card[data-id]').forEach(function (c) { io.observe(c); });
  }

  function hdr() { return { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }; }
  function setCount(id, n) {
    document.querySelectorAll('[data-n="' + id + '"]').forEach(function (el) { el.textContent = n; });
    if (cur == id) title.textContent = n + (n === 1 ? ' comment' : ' comments');
  }
  function clearReply() {
    replyTo = null; bar.textContent = ''; bar.classList.remove('on');
    if (input) input.placeholder = 'Add a comment...';
  }
  function startReply(rootId, name) {
    if (!input) return;
    replyTo = rootId; bar.textContent = '';
    var t = document.createElement('span'); t.textContent = 'Replying to ' + name;
    var x = document.createElement('button'); x.type = 'button'; x.textContent = '\u00d7';
    x.addEventListener('click', function () { clearReply(); input.value = ''; });
    bar.appendChild(t); bar.appendChild(x); bar.classList.add('on');
    input.value = '@' + name + ' '; input.focus();
  }

  function askDel(cid) {
    var w = document.createElement('div'); w.className = 'pf-dlg';
    var c = document.createElement('div'); c.className = 'pf-dlg-c';
    var h = document.createElement('b'); h.textContent = 'Delete this comment?';
    var p = document.createElement('p'); p.textContent = 'Its replies and likes go with it. This cannot be undone.';
    var r = document.createElement('div');
    var no = document.createElement('button'); no.type = 'button'; no.textContent = 'Keep it';
    var yes = document.createElement('button'); yes.type = 'button'; yes.className = 'pf-dlg-yes'; yes.textContent = 'Delete';
    no.addEventListener('click', function () { w.remove(); });
    yes.addEventListener('click', function () { w.remove(); del(cid); });
    w.addEventListener('click', function (e) { if (e.target === w) w.remove(); });
    r.appendChild(no); r.appendChild(yes);
    c.appendChild(h); c.appendChild(p); c.appendChild(r); w.appendChild(c);
    document.body.appendChild(w);
  }

  function row(c, rootId) {
    var d = document.createElement('div'); d.className = 'pf-c' + (c.parent_id ? ' pf-c--r' : ''); d.setAttribute('data-cid', c.id);
    var av = document.createElement('div'); av.className = 'pf-av';
    if (c.avatar) { var im = document.createElement('img'); im.src = c.avatar; im.alt = ''; av.appendChild(im); }
    else av.textContent = (c.name || '?').charAt(0).toUpperCase();
    var b = document.createElement('div'); b.className = 'pf-cb';
    var n = document.createElement('div'); n.className = 'pf-cn'; n.textContent = c.name;
    var ago = document.createElement('i'); ago.textContent = c.ago; n.appendChild(ago);
    var t = document.createElement('div'); t.className = 'pf-ct'; t.textContent = c.body;
    b.appendChild(n); b.appendChild(t);

    var act = document.createElement('div'); act.className = 'pf-ca';
    var lk = document.createElement('button'); lk.type = 'button'; lk.className = 'pf-clike' + (c.liked ? ' on' : '');
    lk.innerHTML = HEART + '<span></span>'; lk.lastChild.textContent = c.likes || 0;
    lk.addEventListener('click', function () {
      var cid = cur; if (!cid) return;
      fetch('/pulse/' + cid + '/comments/' + c.id + '/like', { method: 'POST', headers: hdr(), credentials: 'same-origin' })
        .then(function (r) { if (!r.ok) throw new Error('x'); return r.json(); })
        .then(function (j) { lk.classList.toggle('on', j.liked); lk.lastChild.textContent = j.count; })
        .catch(function () {});
    });
    var rp = document.createElement('button'); rp.type = 'button'; rp.className = 'pf-creply'; rp.textContent = 'Reply';
    rp.addEventListener('click', function () { startReply(rootId, c.name); });
    act.appendChild(lk); act.appendChild(rp);
    if (c.can_delete) {
      var x = document.createElement('button'); x.type = 'button'; x.className = 'pf-del'; x.textContent = 'Delete';
      x.addEventListener('click', function () { askDel(c.id); });
      act.appendChild(x);
    }
    b.appendChild(act);

    if (c.replies && c.replies.length) {
      var rs = document.createElement('div'); rs.className = 'pf-replies';
      c.replies.forEach(function (r) { rs.appendChild(row(r, rootId)); });
      b.appendChild(rs);
    }
    d.appendChild(av); d.appendChild(b);
    return d;
  }
  function render(items) {
    list.textContent = '';
    if (!items.length) {
      var e = document.createElement('div'); e.className = 'pf-empty'; e.textContent = 'No comments yet. Start the conversation.';
      list.appendChild(e); return;
    }
    items.forEach(function (c) { list.appendChild(row(c, c.id)); });
  }
  function load() {
    if (!cur || busy) return;
    fetch('/pulse/' + cur + '/comments', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (busy) return;
        var top = list.scrollTop;
        render(j.comments); setCount(cur, j.count); list.scrollTop = top;
      }).catch(function () {});
  }
  function open(id) {
    cur = id; list.textContent = ''; title.textContent = 'Comments'; clearReply();
    sheet.classList.add('on'); shade.classList.add('on');
    load(); clearInterval(poll); poll = setInterval(load, 8000);
  }
  function close() {
    cur = null; clearInterval(poll); clearReply();
    sheet.classList.remove('on'); shade.classList.remove('on');
    if (input) input.blur();
  }
  function del(cid) {
    fetch('/pulse/' + cur + '/comments/' + cid, { method: 'DELETE', headers: hdr(), credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function () { load(); });
  }

  document.querySelectorAll('.pf-cm').forEach(function (b) {
    b.addEventListener('click', function () { open(b.getAttribute('data-id')); });
  });
  document.querySelectorAll('.rail-btn[aria-label="comment"]').forEach(function (b) {
    b.addEventListener('click', function () { if (active) open(active); });
  });
  shade.addEventListener('click', close);
  document.getElementById('pfClose').addEventListener('click', close);
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });

  if (form) form.addEventListener('submit', function (e) {
    e.preventDefault();
    var v = input.value.trim(); if (!v || !cur) return;
    var btn = document.getElementById('pfSend'); btn.disabled = true; busy = true;
    var fd = new FormData(); fd.append('body', v); if (replyTo) fd.append('parent_id', replyTo);
    fetch('/pulse/' + cur + '/comments', { method: 'POST', body: fd, headers: hdr(), credentials: 'same-origin' })
      .then(function (r) { if (!r.ok) throw new Error('fail'); return r.json(); })
      .then(function (j) { input.value = ''; clearReply(); setCount(cur, j.count); })
      .catch(function () { input.placeholder = 'Could not post. Try again in a moment.'; })
      .finally(function () { btn.disabled = false; busy = false; load(); });
  });
})();

(function () {
  // Mark button: grey at 0, red while it collects marks, green at the threshold. Verified members only.
  var toastEl = null, timer = null;
  function toast(msg) {
    if (!toastEl) { toastEl = document.createElement('div'); toastEl.className = 'pf-toast'; document.body.appendChild(toastEl); }
    toastEl.textContent = msg; toastEl.classList.add('on');
    clearTimeout(timer); timer = setTimeout(function () { toastEl.classList.remove('on'); }, 2600);
  }
  function paint(b) {
    var n = b.querySelector('.pf-ln');
    var c = n ? (parseInt(n.textContent, 10) || 0) : 0;
    var th = parseInt(b.getAttribute('data-th'), 10) || 5;
    b.classList.remove('m-red', 'm-green');
    if (c >= th) b.classList.add('m-green'); else if (c > 0) b.classList.add('m-red');
    b.setAttribute('title', c >= th ? 'Important post' : c + ' of ' + th + ' verified marks');
    var card = b.closest('.pulse-card');
    var badge = card ? card.querySelector('[data-mark-badge]') : null;
    if (badge) badge.style.display = c >= th ? '' : 'none';
  }
  document.querySelectorAll('.pf-like').forEach(function (b) {
    paint(b);
    var n = b.querySelector('.pf-ln');
    if (n && window.MutationObserver) new MutationObserver(function () { paint(b); }).observe(n, { childList: true, characterData: true, subtree: true });
  });
  document.addEventListener('click', function (e) {
    var b = e.target.closest ? e.target.closest('.pf-like') : null; if (!b) return;
    if (b.getAttribute('data-can') !== '1') {
      e.stopImmediatePropagation(); e.preventDefault();
      toast('Only verified members can mark a post as important');
    }
  }, true);
})();
</script>

@auth
@php $pfCanPost = auth()->user()->is_admin || in_array(auth()->user()->tier, ['tier2', 'tier3'], true); @endphp
<div class="pf-up" id="pfUpgrade">
    <div class="pf-up-card">
        <h3>Posting is for Gold members</h3>
        <p>Everyone can watch and comment in Pulse. Upgrade to Gold or Premium to post your own ads and updates.</p>
        <a class="pf-up-go" href="{{ route('subscriptions.index') }}">See plans</a>
        <button type="button" class="pf-up-no" id="pfUpNo">Not now</button>
    </div>
</div>
<script>
(function () {
  var CAN = {{ $pfCanPost ? 'true' : 'false' }}, GO = '{{ route('news.ad.create') }}';
  var up = document.getElementById('pfUpgrade');
  document.body.appendChild(up);
  var no = document.getElementById('pfUpNo');
  if (no) no.addEventListener('click', function () { up.classList.remove('on'); });
  up.addEventListener('click', function (e) { if (e.target === up) up.classList.remove('on'); });
  document.addEventListener('click', function (e) {
    var t = e.target.closest('#pulseComposerTrigger, #pulsePostBtn, .rail-btn--post');
    if (!t) return;
    e.preventDefault(); e.stopImmediatePropagation();
    if (CAN) location.href = GO; else up.classList.add('on');
  }, true);
})();
</script>
@endauth
@endauth
