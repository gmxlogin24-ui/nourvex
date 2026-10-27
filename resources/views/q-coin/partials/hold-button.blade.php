@php
  $qhClaimable = ($canClaimToday ?? false) && !($qcoinLocked ?? false);
  $qhAmount = ($dailyBase ?? 0) + ($nextStreakBonus ?? 0);
  $qhLabel = ($qcoinLocked ?? false)
      ? 'Upgrade to unlock daily claims'
      : (($canClaimToday ?? false) ? 'Hold to claim +' . $qhAmount . ' Q' : 'Already claimed today');
@endphp
<link rel="stylesheet" href="{{ asset('css/qcoin-hold.css') }}?v={{ time() }}">
<script src="{{ asset('js/qcoin-hold.js') }}?v={{ time() }}" defer></script>
<div class="qh-wrap">
  <button type="button" class="qh-btn" data-qc-hold data-action="{{ route('qcoin.claim') }}" data-label-idle="{{ $qhLabel }}" {{ $qhClaimable ? '' : 'disabled' }}>
    <span class="qh-fill" aria-hidden="true"></span>
    <span class="qh-wave" aria-hidden="true"></span>
    <span class="qh-row">
      <span class="qh-ico" aria-hidden="true"><i data-lucide="gift"></i></span>
      <span class="qh-txt">{{ $qhLabel }}</span>
      <span class="qh-dots" aria-hidden="true"><i></i><i></i><i></i></span>
    </span>
  </button>
  <div class="qh-fx" aria-hidden="true"></div>
  <p class="qh-hint">{{ $qhClaimable ? 'Press and hold for 1.5 seconds' : '' }}</p>
</div>
