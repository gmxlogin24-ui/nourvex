@extends('layouts.world-shell')
@section('world', 'gigs')
@section('content')
@php
  $gxBal = (float) (auth()->user()->wallet_balance ?? 0);
  $gxGo = fn ($r) => \Illuminate\Support\Facades\Route::has($r) ? route($r) : '#';
  $gxCats = collect($categories ?? [])->filter()->values();
  $gxIcons = ['Typing & Printing' => 'printer', 'Hair & Beauty' => 'scissors', 'Repairs' => 'wrench', 'Errands & Delivery' => 'truck', 'Tutoring' => 'graduation-cap', 'Photography' => 'camera', 'Laundry' => 'shirt'];
  $gxCat = old('category', $gxCats->first());
  $gxLoc = [['remote', 'Remote', 'laptop'], ['onsite', 'On-site', 'map-pin'], ['pickup', 'Pickup', 'package']];
@endphp
<link rel="stylesheet" href="{{ asset('css/world-pages.css') }}?v={{ @filemtime(public_path('css/world-pages.css')) }}">
<link rel="stylesheet" href="{{ asset('css/gig-post.css') }}?v={{ @filemtime(public_path('css/gig-post.css')) }}">
<link rel="stylesheet" href="{{ asset('css/gig-post2.css') }}?v={{ time() }}">
<main class="wp-page wp-gigs gx">
<form method="POST" action="{{ route('gigs.store') }}" enctype="multipart/form-data" id="gxForm" data-wallet="{{ $gxBal }}">
  @csrf

  <section class="gx-s gx-s0">
    <div class="gx-head">
      <a href="{{ route('gigs.index') }}" class="gx-back" aria-label="Back to hustles"><i data-lucide="arrow-left"></i></a>
      <div><h1>Post a hustle</h1><p>Describe the job, set a price and get it done.</p></div>
    </div>
    <div class="gx-meter">
      <div class="gx-mt"><b id="gxLab">Needs more detail</b><small id="gxTip">Give it a clear title</small></div>
      <div class="gx-bar0"><i id="gxBar"></i></div>
    </div>
  </section>

  @if ($errors->any())
    <div class="gx-err"><i data-lucide="alert-circle"></i><span>{{ $errors->first() }}</span></div>
  @endif

  <section class="gx-s">
    <h2 class="gx-h">The job</h2>
    <div class="gx-f">
      <label for="gpTitle">Title <em id="gxTc">0/80</em></label>
      <input id="gpTitle" type="text" name="title" maxlength="80" required value="{{ old('title') }}" placeholder="e.g. I need my 40-page project typed and bound" class="gx-in">
    </div>
    <div class="gx-f">
      <label>Category</label>
      <select name="category" id="gigCategory" required class="gx-sel">
        @foreach($gxCats as $cat)
          <option value="{{ $cat }}" {{ $gxCat === $cat ? 'selected' : '' }}>{{ $cat }}</option>
        @endforeach
      </select>
      <div class="gx-chips" id="gxCatChips">
        @foreach($gxCats as $cat)
          <button type="button" class="gx-ch {{ $gxCat === $cat ? 'on' : '' }}" data-cat="{{ $cat }}"><i data-lucide="{{ $gxIcons[$cat] ?? 'briefcase' }}"></i>{{ $cat }}</button>
        @endforeach
      </div>
    </div>
    <div id="gigCategoryFields" class="gx-extra"></div>
    <div class="gx-f">
      <label for="gpDesc">Description <em id="gxDc">0 characters</em></label>
      <textarea id="gpDesc" name="description" rows="5" class="gx-in gx-ta" placeholder="What exactly should be done, how many, what quality, and anything the worker must bring or know.">{{ old('description') }}</textarea>
    </div>
  </section>

  <section class="gx-s">
    <h2 class="gx-h">Price</h2>
    <div class="gx-price">
      <span>₦</span>
      <input type="number" name="amount" id="gigAmount" min="1" step="any" inputmode="decimal" value="{{ old('amount') }}" placeholder="0" class="gx-in gx-big">
    </div>
    <label class="gx-sw" for="gigUsePackages">
      <input type="checkbox" id="gigUsePackages" {{ old('packages') ? 'checked' : '' }}><i></i>
      <span><b>Offer packages</b><small>Up to 3 tiers (for example Basic, Standard, Premium). The worker picks one. The lowest tier sets your deposit.</small></span>
    </label>
    <div id="gigPackagesBuilder" style="display:none;" class="gx-pk"></div>
  </section>

  <section class="gx-s">
    <h2 class="gx-h">When and where</h2>
    <div class="gx-f">
      <label for="gpDeadline">Deadline <small>(optional)</small></label>
      <input id="gpDeadline" type="date" name="deadline" value="{{ old('deadline') }}" class="gx-in">
      <div class="gx-chips">
        <button type="button" class="gx-ch" data-days="1">Tomorrow</button>
        <button type="button" class="gx-ch" data-days="3">3 days</button>
        <button type="button" class="gx-ch" data-days="7">1 week</button>
        <button type="button" class="gx-ch" data-days="14">2 weeks</button>
      </div>
    </div>
    <div class="gx-f">
      <label>Where will it happen? <small>(optional)</small></label>
      <div class="gx-seg">
        @foreach($gxLoc as $m)
          <label><input type="radio" name="location_type" value="{{ $m[0] }}" {{ old('location_type') === $m[0] ? 'checked' : '' }}><span><i data-lucide="{{ $m[2] }}"></i>{{ $m[1] }}</span></label>
        @endforeach
      </div>
    </div>
    <div class="gx-f">
      <label for="gpLoc">Location <small>(optional)</small></label>
      <input id="gpLoc" type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Hostel B, or leave blank if remote" class="gx-in">
    </div>
  </section>

  <section class="gx-s">
    <h2 class="gx-h">Contact <small>Optional. Workers can also message you inside Naurvex.</small></h2>
    <div class="gx-two">
      <div class="gx-f"><label for="gpWa">WhatsApp</label><input id="gpWa" type="text" name="whatsapp" inputmode="tel" value="{{ old('whatsapp') }}" placeholder="08012345678" class="gx-in"></div>
      <div class="gx-f"><label for="gpPh">Call number</label><input id="gpPh" type="text" name="phone" inputmode="tel" value="{{ old('phone') }}" placeholder="08012345678" class="gx-in"></div>
    </div>
  </section>

  <section class="gx-s">
    <h2 class="gx-h">Extras</h2>
    <label class="gx-file" for="gpFile"><i data-lucide="paperclip"></i><span id="gpFileName">Attach a brief or reference file<small>Optional, max 2 MB</small></span></label>
    <input type="file" name="attachment" id="gpFile" hidden>
    <label class="gx-sw" for="gigHasItem" style="margin-top:14px">
      <input type="checkbox" name="has_item" id="gigHasItem" value="1" {{ old('has_item') ? 'checked' : '' }}><i></i>
      <span><b>Physical item delivery</b><small>This hustle involves delivering an item</small></span>
    </label>
    <div id="gigItemFields" style="display:none;">
      <div class="gx-f"><label for="gpItem">Item name</label><input id="gpItem" type="text" name="item_name" value="{{ old('item_name') }}" class="gx-in"></div>
      <div class="gx-two">
        <div class="gx-f"><label for="gpQty">Quantity</label><input id="gpQty" type="number" name="item_qty" min="1" value="{{ old('item_qty') }}" class="gx-in"></div>
        <div class="gx-f"><label for="gpCost">Item cost (₦)</label><input id="gpCost" type="number" name="item_cost" min="0" value="{{ old('item_cost') }}" class="gx-in"></div>
      </div>
      <div class="gx-f"><label for="gpAddr">Delivery address</label><input id="gpAddr" type="text" name="delivery_address" value="{{ old('delivery_address') }}" class="gx-in"></div>
      <div class="gx-f">
        <label for="gpDm">Delivery method</label>
        <select id="gpDm" name="item_delivery_method" class="gx-in">
          <option value="">Select</option>
          <option value="meetup" {{ old('item_delivery_method') === 'meetup' ? 'selected' : '' }}>Meet on campus</option>
          <option value="ship" {{ old('item_delivery_method') === 'ship' ? 'selected' : '' }}>Ship / send</option>
        </select>
      </div>
    </div>
  </section>

  <section class="gx-s">
    <h2 class="gx-h">How workers will see it</h2>
    <div class="gx-pv">
      <span class="gx-pvi" id="pvIcon"><i data-lucide="briefcase"></i></span>
      <span class="gx-pvb">
        <span class="gx-pvtags"><em id="pvCat">Category</em><em id="pvLoc" hidden></em></span>
        <b id="pvT">Your title appears here</b>
        <strong id="pvP">₦0</strong>
        <small id="pvD">No deadline</small>
      </span>
    </div>
  </section>

  <div class="gx-wal">
    <span class="gx-wi"><i data-lucide="wallet"></i></span>
    <span class="gx-wt"><b>Wallet ₦{{ number_format($gxBal) }}</b><small id="gxDep">Deposit now: ₦0 (50%). The rest is held until you approve the work.</small></span>
    <a href="{{ $gxGo('wallet.index') }}">Top up</a>
  </div>

  <div class="gx-bar">
    <button type="submit" class="btn-primary gx-go" id="gxSubmit" onclick="return confirm('Post this gig? 50% of the amount (or the lowest package price) will be held from your wallet immediately.');"><i data-lucide="send"></i><span>Post hustle</span></button>
  </div>
</form>
</main>

<script>
(function () {
  var f = document.getElementById('gxForm'); if (!f) return;
  var wallet = parseFloat(f.getAttribute('data-wallet')) || 0;
  var sel = document.getElementById('gigCategory');
  function val(id) { var e = document.getElementById(id); return e ? e.value.trim() : ''; }
  function ymd(d) { return d.getFullYear() + '-' + ('0' + (d.getMonth() + 1)).slice(-2) + '-' + ('0' + d.getDate()).slice(-2); }

  [].slice.call(f.querySelectorAll('[data-cat]')).forEach(function (b) {
    b.addEventListener('click', function () {
      sel.value = b.getAttribute('data-cat');
      sel.dispatchEvent(new Event('change', { bubbles: true }));
      update();
    });
  });
  [].slice.call(f.querySelectorAll('[data-days]')).forEach(function (b) {
    b.addEventListener('click', function () {
      var d = new Date(); d.setDate(d.getDate() + parseInt(b.getAttribute('data-days'), 10));
      document.getElementById('gpDeadline').value = ymd(d);
      update();
    });
  });
  var fi = document.getElementById('gpFile'), fn = document.getElementById('gpFileName');
  if (fi && fn) fi.addEventListener('change', function () { fn.textContent = fi.files && fi.files[0] ? fi.files[0].name : 'Attach a brief or reference file'; });
  [].forEach.call(f.querySelectorAll('.gx-seg label'), function (l) {
    var r = l.querySelector('input'), was = false;
    l.addEventListener('pointerdown', function () { was = r.checked; });
    r.addEventListener('click', function () { if (was) { r.checked = false; was = false; update(); } });
  });

  function price() {
    var pk = document.getElementById('gigUsePackages');
    if (pk && pk.checked) {
      var vals = [].slice.call(f.querySelectorAll('input[name^="packages"][name$="[price]"]')).map(function (i) { return parseFloat(i.value); }).filter(function (n) { return n > 0; });
      if (vals.length) return { v: Math.min.apply(null, vals), from: true };
    }
    return { v: parseFloat(val('gigAmount')) || 0, from: false };
  }

  function update() {
    var title = val('gpTitle'), desc = val('gpDesc'), pr = price(), dl = val('gpDeadline');
    var cat = sel ? sel.value : '';
    var lt = f.querySelector('input[name=location_type]:checked');
    document.getElementById('gxTc').textContent = title.length + '/80';
    document.getElementById('gxDc').textContent = desc.length + ' characters';
    [].slice.call(f.querySelectorAll('[data-cat]')).forEach(function (b) { b.classList.toggle('on', b.getAttribute('data-cat') === cat); });

    document.getElementById('pvT').textContent = title || 'Your title appears here';
    document.getElementById('pvP').textContent = (pr.from ? 'Starting at ' : '') + '₦' + (pr.v ? pr.v.toLocaleString('en-NG') : '0');
    document.getElementById('pvCat').textContent = cat || 'Category';
    var pl = document.getElementById('pvLoc');
    pl.hidden = !lt; if (lt) pl.textContent = { remote: 'Remote', onsite: 'On-site', pickup: 'Pickup' }[lt.value];
    document.getElementById('pvD').textContent = dl ? 'Due ' + new Date(dl + 'T00:00:00').toLocaleDateString('en-NG', { day: 'numeric', month: 'short' }) : 'No deadline';

    var dep = Math.round(pr.v * 0.5 * 100) / 100;
    var de = document.getElementById('gxDep');
    if (pr.v && dep > wallet) { de.textContent = 'Deposit ₦' + dep.toLocaleString('en-NG') + ' is more than your wallet. Top up first.'; de.className = 'bad'; }
    else { de.textContent = 'Deposit now: ₦' + dep.toLocaleString('en-NG') + ' (50%). The rest is held until you approve the work.'; de.className = ''; }

    var s = 0, tips = [];
    if (title.length >= 12) s += 25; else tips.push('Give it a clear title');
    if (cat) s += 10;
    if (desc.length >= 80) s += 30; else tips.push('Describe the job in more detail');
    if (pr.v > 0) s += 20; else tips.push('Set a price');
    if (dl) s += 10; else tips.push('Add a deadline');
    if (val('gpWa') || val('gpPh')) s += 5;
    document.getElementById('gxBar').style.width = s + '%';
    document.getElementById('gxLab').textContent = s < 40 ? 'Needs more detail' : (s < 75 ? 'Good hustle' : 'Excellent hustle');
    document.getElementById('gxTip').textContent = tips.length ? tips[0] : 'Looks great. Ready to post.';
  }

  f.addEventListener('input', update);
  f.addEventListener('change', update);
  f.addEventListener('submit', function () {
    var b = document.getElementById('gxSubmit'); b.disabled = true;
    var sp = b.querySelector('span'); if (sp) sp.textContent = 'Posting...';
  });
  var gigAmountRef = document.getElementById('gigAmount');
  update();
  if (window.lucide) lucide.createIcons();
})();
</script>
<script>
const gigCategoryFields = @json(config('gig_categories'));
const gigOldPackages = @json(old('packages', []));

function gigPackageRow(index, pkg) {
    pkg = pkg || {};
    const wrap = document.createElement('div');
    wrap.className = 'glass-card p-3 space-y-2';
    wrap.innerHTML =
        '<input type="text" name="packages[' + index + '][name]" placeholder="Tier name (e.g. Basic)" class="input-field" value="' + (pkg.name || '') + '">' +
        '<input type="number" name="packages[' + index + '][price]" min="1" placeholder="Price (₦)" class="input-field" value="' + (pkg.price || '') + '">' +
        '<input type="text" name="packages[' + index + '][description]" placeholder="What\'s included (optional)" class="input-field" value="' + (pkg.description || '') + '">';
    return wrap;
}

function gigInitPackagesBuilder(checkboxId, containerId, amountId) {
    const checkbox = document.getElementById(checkboxId);
    const container = document.getElementById(containerId);
    const amountInput = amountId ? document.getElementById(amountId) : null;
    if (!checkbox || !container) return;

    function refreshAmountRequired() {
        if (!amountInput) return;
        if (checkbox.checked) {
            amountInput.removeAttribute('required');
        } else {
            amountInput.setAttribute('required', 'required');
        }
    }

    function toggle() {
        container.style.display = checkbox.checked ? 'block' : 'none';
        if (checkbox.checked && container.children.length === 0) {
            for (let i = 0; i < 3; i++) {
                container.appendChild(gigPackageRow(i, gigOldPackages[i]));
            }
        }
        refreshAmountRequired();
    }

    checkbox.addEventListener('change', toggle);
    toggle();
    if (gigOldPackages.length) {
        checkbox.checked = true;
        toggle();
    }
}
function renderGigCategoryFields(selected) {
    const container = document.getElementById('gigCategoryFields');
    if (!container) return;
    container.innerHTML = '';
    const fields = (gigCategoryFields[selected] && gigCategoryFields[selected].fields) || [];
    fields.forEach(function (f) {
        const wrap = document.createElement('div');
        const label = document.createElement('label');
        label.className = 'text-sm text-muted';
        label.textContent = f.label;
        wrap.appendChild(label);
        let input;
        if (f.type === 'select' && f.options) {
            input = document.createElement('select');
            input.className = 'input-field !mt-1';
            const blank = document.createElement('option');
            blank.value = '';
            blank.textContent = '— Select —';
            input.appendChild(blank);
            f.options.forEach(function (opt) {
                const o = document.createElement('option');
                o.value = opt;
                o.textContent = opt;
                input.appendChild(o);
            });
        } else {
            input = document.createElement('input');
            input.type = 'text';
            input.className = 'input-field !mt-1';
        }
        input.name = 'details[' + f.key + ']';
        wrap.appendChild(input);
        container.appendChild(wrap);
    });
}
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('gigCategory');
    if (sel) {
        renderGigCategoryFields(sel.value);
        sel.addEventListener('change', function () { renderGigCategoryFields(sel.value); });
    }
    const hasItem = document.getElementById('gigHasItem');
    const itemFields = document.getElementById('gigItemFields');
    if (hasItem && itemFields) {
        const toggle = function () { itemFields.style.display = hasItem.checked ? 'block' : 'none'; };
        toggle();
        hasItem.addEventListener('change', toggle);
    }
    gigInitPackagesBuilder('gigUsePackages', 'gigPackagesBuilder', 'gigAmount');
});
</script>
@endsection
