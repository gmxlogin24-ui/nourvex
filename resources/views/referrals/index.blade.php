@extends('layouts.app-authenticated')
@section('title', 'Referrals')
@section('content')
@php
  $style = auth()->user()->theme_style ?? 'glass';
  $isPlain = $style === 'plain';
  $cardClass = $isPlain ? 'dqm-card' : 'glass-card';
  $heroClass = $isPlain ? 'dqm-hero' : 'hero-blob';
  $inviteUrl = request()->getSchemeAndHttpHost() . '/register?ref=' . rawurlencode((string) $code);
  $shareMsg = 'Join me on NAURVEX. Sign up with my invite code ' . $code . '.';
  $btn = 'display:inline-flex;align-items:center;justify-content:center;padding:0.6rem 1rem;border-radius:999px;border:1.5px solid var(--border-color,#e2e2e2);background:var(--color-card,#fff);color:var(--color-text,#0F1115);font-weight:600;font-size:0.85rem;cursor:pointer;text-decoration:none;';
  $btnAccent = 'display:inline-flex;align-items:center;justify-content:center;padding:0.6rem 1rem;border-radius:999px;border:none;background:var(--user-accent,#0F9D6E);color:#fff;font-weight:700;font-size:0.85rem;cursor:pointer;text-decoration:none;';
@endphp
<main class="max-w-3xl mx-auto p-6">
  <h1 class="text-2xl font-bold mb-1">Your referrals</h1>
  <p class="text-sm mb-6" style="color:var(--color-muted)">Earn a share when people you invite complete gigs or upgrade their tier.</p>

  <section class="{{ $heroClass }} p-6 mb-6">
    <p class="text-white/80 text-sm">Your referral code</p>
    <p class="text-2xl font-bold text-white mt-1" id="refCode">{{ $code }}</p>
    <p class="text-white/70 text-xs mt-2">Share this when friends sign up</p>
  </section>

  <section class="{{ $cardClass }} p-6 mb-6">
    <h2 class="font-semibold mb-1">Invite someone</h2>
    <p class="text-sm mb-4" style="color:var(--color-muted)">Send them your link. Anyone who signs up with it is counted as your referral.</p>

    <input type="text" id="inviteUrl" readonly value="{{ $inviteUrl }}"
      style="width:100%;padding:0.6rem 0.8rem;border-radius:0.6rem;border:1px solid var(--border-color,#e2e2e2);background:transparent;color:inherit;font-size:0.85rem;margin-bottom:0.9rem;">

    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:0.9rem;">
      <button type="button" id="shareLink" style="{{ $btnAccent }}">Share invite</button>
      <button type="button" id="copyLink" style="{{ $btn }}">Copy link</button>
      <button type="button" id="copyCode" style="{{ $btn }}">Copy code</button>
    </div>

    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
      <a href="https://wa.me/?text={{ rawurlencode($shareMsg . ' ' . $inviteUrl) }}" target="_blank" rel="noopener" style="{{ $btn }}">WhatsApp</a>
      <a href="sms:?&body={{ rawurlencode($shareMsg . ' ' . $inviteUrl) }}" style="{{ $btn }}">Text message</a>
      <a href="mailto:?subject={{ rawurlencode('Join me on NAURVEX') }}&body={{ rawurlencode($shareMsg . ' ' . $inviteUrl) }}" style="{{ $btn }}">Email</a>
    </div>
    <p id="shareStatus" class="text-xs mt-3" style="color:var(--color-muted);min-height:1em;"></p>
  </section>

  <section class="{{ $cardClass }} p-6">
    <h2 class="font-semibold mb-4">Referred people ({{ count($referrals) }})</h2>
    @if(count($referrals) === 0)
      <p class="text-sm" style="color:var(--color-muted)">No referrals yet. Send your invite above to start earning.</p>
    @else
      <ul class="space-y-3">
        @foreach($referrals as $r)
          <li class="flex items-center justify-between text-sm pb-3" style="border-bottom:1px solid var(--border-color,#e2e2e2);">
            <span>{{ $r->name }}</span>
            <span class="text-xs" style="color:var(--color-muted)">{{ strtoupper($r->tier) }}</span>
          </li>
        @endforeach
      </ul>
    @endif
  </section>
</main>

<script>
(function () {
  var status = document.getElementById('shareStatus');
  var url = document.getElementById('inviteUrl').value;
  var code = document.getElementById('refCode').textContent.trim();
  var msg = {!! json_encode($shareMsg) !!};

  function say(t) {
    status.textContent = t;
    setTimeout(function () { status.textContent = ''; }, 2000);
  }
  function copy(text, done) {
    if (navigator.clipboard && window.isSecureContext) {
      navigator.clipboard.writeText(text).then(function () { say(done); }, function () { fallback(text, done); });
    } else {
      fallback(text, done);
    }
  }
  function fallback(text, done) {
    var ta = document.createElement('textarea');
    ta.value = text;
    ta.setAttribute('readonly', '');
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    var ok = false;
    try { ok = document.execCommand('copy'); } catch (e) {}
    document.body.removeChild(ta);
    say(ok ? done : 'Could not copy. Select the text and copy it.');
  }

  document.getElementById('copyLink').addEventListener('click', function () {
    copy(url, 'Link copied');
  });
  document.getElementById('copyCode').addEventListener('click', function () {
    copy(code, 'Code copied');
  });
  document.getElementById('shareLink').addEventListener('click', function () {
    if (navigator.share) {
      navigator.share({ title: 'Join me on NAURVEX', text: msg + ' ' + url }).catch(function () {});
    } else {
      copy(url, 'Link copied. Paste it to share.');
    }
  });
})();
</script>
@endsection
