@extends('layouts.app-authenticated')
@section('title', 'Post an Ad')
@section('content')
@php
    $u = auth()->user();
    $free = (bool) $u->is_admin;
    $qRate = (int) config('qcoin.q_per_naira_display', 20);
    $walletBal = (float) $u->wallet_balance;
    $qBal = (int) $u->q_coin_balance;
    $firstDays = old('duration', array_key_first($pricing));
    $opts = [];
    foreach ($pricing as $days => $naira) {
        $naira = (float) $naira;
        $qCost = (int) round($naira * $qRate);
        $wOk = $free || (!$u->wallet_restricted && $walletBal >= $naira && !\App\Services\SpendLimit::blockedReason($u, $naira));
        $qOk = $free || $qBal >= $qCost;
        $opts[$days] = ['naira' => $naira, 'q' => $qCost, 'wallet' => $wOk, 'qok' => $qOk];
    }
@endphp
<link rel="stylesheet" href="{{ asset('css/ad-create.css') }}?v={{ time() }}">

<main class="ad-page">
    <a href="{{ route('pulse.index') }}" class="ad-back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M15 6l-6 6 6 6"/></svg> Back to Pulse
    </a>
    <h1 class="ad-h1">Post an ad</h1>
    <p class="ad-sub">Your ad shows in Pulse for every verified student. Fill it in and watch the preview update.</p>

    @if ($errors->any())<div class="ad-err">{{ $errors->first() }}</div>@endif

    <div class="ad-prev-label">Live preview</div>
    <div class="ad-prev" id="adPrev">
        <div class="ad-prev-in">
            <div class="ad-prev-top"><span id="pvCat">Category</span><span class="ad-pill">Sponsored</span><span class="ad-pill" id="pvEnds"></span></div>
            <h3 id="pvTitle">Your headline</h3>
            <p id="pvBody">Your ad copy appears here.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('news.ad.store') }}" enctype="multipart/form-data" id="adForm">
        @csrf

        <section class="ad-card">
            <h2>The ad</h2>
            <div class="ad-f">
                <label for="adTitle">Headline <em id="tCount">0/80</em></label>
                <input class="ad-in" id="adTitle" type="text" name="title" required maxlength="80" value="{{ old('title') }}" placeholder="e.g. Fresh shawarma, 20% off today">
            </div>
            <div class="ad-f">
                <label for="adCat">Category</label>
                <select class="ad-in" id="adCat" name="category" required>
                    <option value="" disabled {{ old('category') ? '' : 'selected' }}>Choose a category</option>
                    @foreach(\App\Http\Controllers\NewsController::AD_CATEGORIES as $cat)
                    <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ad-f">
                <label for="adBody">Ad copy</label>
                <textarea class="ad-in" id="adBody" name="body" required maxlength="600" placeholder="Tell students what you offer and how to reach you.">{{ old('body') }}</textarea>
            </div>
            <div class="ad-f">
                <label>Image <em>optional</em></label>
                <div class="ad-up">
                    <input type="file" name="image" id="adImg" accept="image/*">
                    <span class="ad-up-ic"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V5M7 9l5-5 5 5M5 19h14"/></svg></span>
                    <span><b id="adImgName">Add a picture</b><small>Landscape works best. Up to 6 MB.</small></span>
                </div>
            </div>
        </section>

        <section class="ad-card">
            <h2>How long should it run?</h2>
            <div class="ad-f">
                <div class="ad-dur">
                    @foreach($opts as $days => $o)
                    <label class="ad-d">
                        <input type="radio" name="duration" value="{{ $days }}"
                            data-naira="{{ $o['naira'] }}" data-q="{{ $o['q'] }}"
                            data-wallet="{{ $o['wallet'] ? 1 : 0 }}" data-qok="{{ $o['qok'] ? 1 : 0 }}"
                            {{ (int) $firstDays === (int) $days ? 'checked' : '' }} required>
                        <span><b>{{ $days }} days</b><i>₦{{ number_format($o['naira']) }}</i><small>or {{ number_format($o['q']) }} Q</small></span>
                    </label>
                    @endforeach
                </div>
            </div>
        </section>

        @if($u->tier === 'tier3' || $free)
        <div class="ad-note">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex:none;margin-top:1px"><path d="M3 8l4 4 5-7 5 7 4-4-2 11H5L3 8z"/></svg>
            <span>Premium perk: your ad is pinned to the top of Pulse and marked Featured.</span>
        </div>
        @endif

        <div class="ad-sum">
            <div class="ad-row"><span>Runs for</span><b id="sumDays">{{ $firstDays }} days</b></div>
            @unless($free)
            <div class="ad-row"><span>Wallet balance</span><b>₦{{ number_format($walletBal) }}</b></div>
            <div class="ad-row"><span>Q Coin balance</span><b>{{ number_format($qBal) }} Q</b></div>
            @endunless
            <div class="ad-row"><span>Pay with</span><b id="sumPay"></b></div>
            <div class="ad-row tot"><span>{{ $free ? 'Cost' : 'Total' }}</span><b id="sumTot"></b></div>
        </div>

        <button type="submit" class="ad-go" id="adGo">{{ $free ? 'Publish ad' : 'Pay and go live' }}</button>
        <a href="{{ route('wallet.deposit') }}" class="ad-go" id="adTop" style="display:none;text-align:center;text-decoration:none;">Top up wallet</a>
    </form>
</main>

<script>
(function () {
  var FREE = {{ $free ? 'true' : 'false' }};
  var $ = function (id) { return document.getElementById(id); };
  function money(n) { return Math.round(n).toLocaleString(); }
  function txt(id, v, d) { $(id).textContent = v && v.trim() ? v : d; }

  function sync() {
    txt('pvTitle', $('adTitle').value, 'Your headline');
    txt('pvCat', $('adCat').value, 'Category');
    txt('pvBody', $('adBody').value, 'Your ad copy appears here.');
    $('tCount').textContent = $('adTitle').value.length + '/80';
  }
  ['adTitle', 'adCat', 'adBody'].forEach(function (id) { $(id).addEventListener('input', sync); });
  $('adCat').addEventListener('change', sync);

  function price() {
    var r = document.querySelector('input[name="duration"]:checked');
    if (!r) return;
    var days = r.value;
    var naira = parseFloat(r.getAttribute('data-naira')) || 0;
    var q = parseFloat(r.getAttribute('data-q')) || 0;
    var wOk = r.getAttribute('data-wallet') === '1';
    var qOk = r.getAttribute('data-qok') === '1';
    var go = $('adGo'), top = $('adTop');

    $('sumDays').textContent = days + ' days';
    $('pvEnds').textContent = days + ' day run';

    if (FREE) {
      $('sumPay').textContent = 'Free (admin)';
      $('sumTot').textContent = 'Free for you';
      $('sumTot').classList.remove('ad-low');
      go.disabled = false; top.style.display = 'none';
      return;
    }
    if (wOk) {
      $('sumPay').textContent = 'Wallet';
      $('sumTot').textContent = '₦' + money(naira);
      $('sumTot').classList.remove('ad-low');
      go.disabled = false; top.style.display = 'none';
    } else if (qOk) {
      $('sumPay').textContent = 'Q Coin';
      $('sumTot').textContent = money(q) + ' Q';
      $('sumTot').classList.remove('ad-low');
      go.disabled = false; top.style.display = 'none';
    } else {
      $('sumPay').textContent = 'Not enough. Top up your wallet.';
      $('sumTot').textContent = '₦' + money(naira);
      $('sumTot').classList.add('ad-low');
      go.disabled = true; top.style.display = 'block';
    }
  }
  document.querySelectorAll('input[name="duration"]').forEach(function (r) { r.addEventListener('change', price); });

  $('adImg').addEventListener('change', function () {
    var f = this.files && this.files[0];
    if (!f) { $('adImgName').textContent = 'Add a picture'; return; }
    $('adImgName').textContent = f.name;
    $('adPrev').style.setProperty('background-image', 'url(' + URL.createObjectURL(f) + ')', 'important');
  });

  $('adForm').addEventListener('submit', function () {
    var b = $('adGo');
    setTimeout(function () { b.disabled = true; b.textContent = 'Please wait...'; }, 0);
  });

  sync(); price();
})();
</script>
@endsection
