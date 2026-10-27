@php
  $qcTier = $qcTier ?? 'gold';
  $qcSize = $qcSize ?? 'lg';
  $qcLogo = asset('images/qcoin-logo.svg') . '?v=3';
@endphp
<link rel="stylesheet" href="{{ asset('css/qcoin-coin.css') }}?v={{ time() }}">
<script src="{{ asset('js/qcoin-hero.js') }}?v={{ time() }}" defer></script>
<div class="qc qc-{{ $qcTier }} qc-{{ $qcSize }}" data-qc3 role="img" aria-label="{{ ucfirst($qcTier) }} Q coin, tap to flip">
  <span class="qc-halo" aria-hidden="true"></span>
  <div class="qc-float">
    <div class="qc-spin">
      <div class="qc-stage">
        @for($i = 1; $i <= 7; $i++)
          <span class="qc-edge" style="--d: {{ $i * 1.6 }}px" aria-hidden="true"></span>
        @endfor

        <span class="qc-face">
          <span class="qc-ring" aria-hidden="true"></span>
          <img class="qc-emblem" src="{{ $qcLogo }}" alt="" aria-hidden="true" draggable="false">
          <span class="qc-gloss" aria-hidden="true"></span>
        </span>

        <span class="qc-back" aria-hidden="true">
          <span class="qc-ring"></span>
          <span class="qc-brand">NAURVEX</span>
          <span class="qc-bar"></span>
          <span class="qc-gloss"></span>
        </span>
      </div>
    </div>
  </div>
  <span class="qc-shadow" aria-hidden="true"></span>
</div>
