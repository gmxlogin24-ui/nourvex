@extends('layouts.world-shell')
@section('world', 'market')
@section('title', 'Post a listing')
@section('content')
@php
  $cats = ['Fashion' => 'shirt', 'Electronics' => 'cpu', 'Books' => 'book', 'Food' => 'utensils', 'Services' => 'wrench', 'Rentals' => 'key', 'Other' => 'grid'];
  $types = [
    'item' => ['Item', 'shopping-bag', 'Sell a product'],
    'service' => ['Service', 'wrench', 'Tailoring, repairs, tutoring'],
    'rent' => ['Rental', 'key', 'Rooms, gadgets, gear'],
    'store' => ['Store', 'store', 'A shop with many products'],
  ];
  $conds = ['New', 'Like New', 'Used', 'N/A'];
  $canStock = in_array(auth()->user()->tier, ['tier2', 'tier3'], true);
  $oType = old('listing_type', 'item');
  $oCat = old('category', 'Fashion');
  $oCond = old('condition', 'New');
  $oDel = old('delivery_method', 'meetup');
@endphp
<link rel="stylesheet" href="{{ asset('css/market-new.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/shop-pages.css') }}?v={{ time() }}">
<link rel="stylesheet" href="{{ asset('css/mk-post.css') }}?v={{ time() }}">
<main class="sp pfm">
<form method="POST" action="{{ route('marketplace.store') }}" enctype="multipart/form-data" id="pfForm" novalidate-off>
  @csrf

  <section class="sp-s sp-s0">
    <div class="sp-head">
      <a href="{{ route('marketplace.index') }}" class="sp-back" aria-label="Back to market"><i data-lucide="arrow-left"></i></a>
      <h1>Post a listing</h1>
      @if(!is_null($remaining ?? null))<span>{{ $remaining }} left this week</span>@endif
    </div>
    <div class="pf-meter">
      <div class="pf-mt"><b id="pfLab">Needs more detail</b><small id="pfTip">Add at least one photo</small></div>
      <div class="pf-bar0"><i id="pfBar"></i></div>
    </div>
  </section>

  @if ($errors->any())
    <div class="sp-flash bad">
      @foreach(array_slice($errors->all(), 0, 3) as $pfE)<div>{{ $pfE }}</div>@endforeach
    </div>
  @endif

  <section class="sp-s">
    <h2 class="sp-h">What are you posting?</h2>
    <div class="pf-types">
      @foreach($types as $val => $t)
        <label class="pf-ty">
          <input type="radio" name="listing_type" value="{{ $val }}" {{ $oType === $val ? 'checked' : '' }} required>
          <span><i data-lucide="{{ $t[1] }}"></i><b>{{ $t[0] }}</b><small>{{ $t[2] }}</small></span>
        </label>
      @endforeach
    </div>
    <div class="pf-f" data-types="store" style="margin-top:12px">
      <label for="pfStore">Store name</label>
      <input type="text" name="store_name" id="pfStore" class="pf-in" maxlength="255" value="{{ old('store_name') }}" placeholder="e.g. Mama Blessing Provisions">
    </div>
  </section>

  <section class="sp-s">
    <h2 class="sp-h">Photos <small>First photo is the cover. Add front, side, top and back so buyers see every angle. Up to 6.</small></h2>
    <div class="pf-photos" id="pfPhotos">
      <label class="pf-add" id="pfAdd">
        <i data-lucide="camera"></i><b>Add photos</b><small id="pfCount">0/6</small>
        <input type="file" id="pfFile" accept="image/*" multiple hidden>
      </label>
    </div>
    <input type="file" name="images[]" id="pfReal" accept="image/*" multiple hidden>
    <p class="pf-warn" id="pfWarn"></p>
  </section>

  <section class="sp-s">
    <h2 class="sp-h">Basics</h2>
    <div class="pf-f">
      <label for="pfTitle">Title <em id="pfTc">0/80</em></label>
      <input type="text" name="title" id="pfTitle" class="pf-in" maxlength="80" required value="{{ old('title') }}" placeholder="e.g. Samsung Galaxy A14, 128GB, black">
    </div>
    <div class="pf-f">
      <label>Category</label>
      <div class="pf-chips">
        @foreach($cats as $name => $icon)
          <label class="pf-ch"><input type="radio" name="category" value="{{ $name }}" {{ $oCat === $name ? 'checked' : '' }} required><span><i data-lucide="{{ $icon }}"></i>{{ $name }}</span></label>
        @endforeach
      </div>
    </div>
    <div class="pf-f" data-types="item">
      <label>Condition</label>
      <div class="pf-chips">
        @foreach($conds as $c)
          <label class="pf-ch"><input type="radio" name="condition" value="{{ $c }}" {{ $oCond === $c ? 'checked' : '' }} required><span>{{ $c }}</span></label>
        @endforeach
      </div>
    </div>
    <div class="pf-f" data-types="item,store">
      <label for="pfBrand">Brand <small>(optional)</small></label>
      <input type="text" name="brand" id="pfBrand" class="pf-in" maxlength="100" value="{{ old('brand') }}" placeholder="e.g. Samsung">
    </div>
  </section>

  <section class="sp-s">
    <h2 class="sp-h">Price</h2>
    <div class="pf-price">
      <span>₦</span>
      <input type="number" name="amount" id="pfPrice" class="pf-in pf-big" min="0" step="any" inputmode="decimal" required value="{{ old('amount') }}" placeholder="0">
    </div>
    <label class="pf-sw"><input type="checkbox" name="negotiable" value="1" id="pfNeg" {{ old('negotiable') ? 'checked' : '' }}><i></i><span>Price is negotiable</span></label>
    <div class="pf-f" data-types="item,store" style="margin-top:12px">
      <label for="pfOld">Old price <small>(optional, shows a discount badge)</small></label>
      <input type="number" name="old_price" id="pfOld" class="pf-in" min="0" step="any" inputmode="decimal" value="{{ old('old_price') }}" placeholder="e.g. 25000">
    </div>
    @if($canStock)
      <div class="pf-f" data-types="item,store" style="margin-top:12px">
        <label for="pfStock">Stock quantity <small>(leave blank for a single one-off item)</small></label>
        <input type="number" name="stock_quantity" id="pfStock" class="pf-in" min="1" inputmode="numeric" value="{{ old('stock_quantity') }}" placeholder="e.g. 10">
      </div>
    @endif
  </section>

  <section class="sp-s">
    <h2 class="sp-h">Details <small>This is what turns a browser into a buyer.</small></h2>
    <div class="pf-f">
      <label for="pfDesc">Description <em id="pfDc">0 characters</em></label>
      <textarea name="description" id="pfDesc" class="pf-in pf-ta" rows="5" placeholder="What is it, how was it used, why are you selling, anything the buyer should know...">{{ old('description') }}</textarea>
    </div>
    <div class="pf-f">
      <label for="pfHi">Highlights <small>(one per line)</small></label>
      <textarea name="highlights" id="pfHi" class="pf-in pf-ta" rows="3" maxlength="1500" placeholder="Battery lasts 2 days&#10;Screen has no scratches">{{ old('highlights') }}</textarea>
    </div>
    <div class="pf-f" data-types="item,store">
      <label for="pfBox">What's in the box <small>(one per line)</small></label>
      <textarea name="whats_in_box" id="pfBox" class="pf-in pf-ta" rows="2" maxlength="1000" placeholder="Charger&#10;Original box">{{ old('whats_in_box') }}</textarea>
    </div>
    <div class="pf-two" data-types="item,store">
      <div class="pf-f">
        <label for="pfCol">Colors <small>(comma separated)</small></label>
        <input type="text" name="colors" id="pfCol" class="pf-in" maxlength="255" value="{{ old('colors') }}" placeholder="Black, Blue, White">
      </div>
      <div class="pf-f">
        <label for="pfSiz">Sizes <small>(comma separated)</small></label>
        <input type="text" name="sizes" id="pfSiz" class="pf-in" maxlength="255" value="{{ old('sizes') }}" placeholder="S, M, L or 40, 41, 42">
      </div>
    </div>
    <div class="pf-f" data-types="item,store">
      <label for="pfSpec">Specifications <small>(one per line, as Label: value)</small></label>
      <textarea name="specs" id="pfSpec" class="pf-in pf-ta" rows="4" maxlength="1500" placeholder="Material: Leather&#10;Weight: 600g">{{ old('specs') }}</textarea>
    </div>
    <div class="pf-two">
      <div class="pf-f">
        <label for="pfWar">Warranty <small>(optional)</small></label>
        <input type="text" name="warranty" id="pfWar" class="pf-in" maxlength="100" value="{{ old('warranty') }}" placeholder="e.g. 3 months">
      </div>
    </div>
    <div class="pf-f">
      <label for="pfRet">Return policy <small>(optional)</small></label>
      <textarea name="return_policy" id="pfRet" class="pf-in pf-ta" rows="2" maxlength="1000" placeholder="e.g. Returns accepted within 2 days if faulty">{{ old('return_policy') }}</textarea>
    </div>
  </section>

  <section class="sp-s">
    <h2 class="sp-h">Delivery &amp; location</h2>
    <div class="pf-del">
      <label class="pf-ty"><input type="radio" name="delivery_method" value="meetup" {{ $oDel === 'meetup' ? 'checked' : '' }} required><span><i data-lucide="handshake"></i><b>Meet on campus</b><small>Buyer comes to you</small></span></label>
      <label class="pf-ty"><input type="radio" name="delivery_method" value="ship" {{ $oDel === 'ship' ? 'checked' : '' }} required><span><i data-lucide="truck"></i><b>I'll ship it</b><small>Buyer may be far</small></span></label>
    </div>
    <div class="pf-f" style="margin-top:12px">
      <label for="pfLoc">Location</label>
      <input type="text" name="location" id="pfLoc" class="pf-in" maxlength="100" required value="{{ old('location') }}" placeholder="e.g. DELSU Abraka">
    </div>
  </section>

  <section class="sp-s">
    <h2 class="sp-h">Contact <small>Buyers can also message you inside Naurvex.</small></h2>
    <div class="pf-two">
      <div class="pf-f">
        <label for="pfWa">WhatsApp</label>
        <input type="text" name="whatsapp" id="pfWa" class="pf-in" maxlength="20" inputmode="tel" value="{{ old('whatsapp') }}" placeholder="08012345678">
      </div>
      <div class="pf-f">
        <label for="pfPh">Call number</label>
        <input type="text" name="phone" id="pfPh" class="pf-in" maxlength="20" inputmode="tel" value="{{ old('phone') }}" placeholder="08012345678">
      </div>
    </div>
  </section>

  <section class="sp-s">
    <h2 class="sp-h">How buyers will see it</h2>
    <div class="pf-pv">
      <span class="pf-pvi" id="pvI"><i data-lucide="image"></i></span>
      <span class="pf-pvb">
        <span class="pf-pvtags"><em id="pvTag">Item</em><em id="pvNeg" hidden>Negotiable</em></span>
        <b id="pvT">Your title appears here</b>
        <strong id="pvP">₦0</strong>
        <small><i data-lucide="map-pin"></i><span id="pvL">Location</span></small>
      </span>
    </div>
  </section>

  <div class="pf-bar">
    <button type="submit" class="btn-primary pf-go" id="pfSubmit"><i data-lucide="send"></i><span>Post listing</span></button>
  </div>
</form>
</main>

<script>
(function () {
  var f = document.getElementById('pfForm'); if (!f) return;
  var MAX = 6, LIM = 4 * 1024 * 1024;
  var dt = new DataTransfer();
  var real = document.getElementById('pfReal'), pick = document.getElementById('pfFile');
  var grid = document.getElementById('pfPhotos'), add = document.getElementById('pfAdd');
  var cnt = document.getElementById('pfCount'), warn = document.getElementById('pfWarn');
  var pvI = document.getElementById('pvI'), pvT = document.getElementById('pvT'), pvP = document.getElementById('pvP');
  var pvL = document.getElementById('pvL'), pvTag = document.getElementById('pvTag'), pvNeg = document.getElementById('pvNeg');
  var lastCov = null;

  function val(id) { var e = document.getElementById(id); return e ? e.value.trim() : ''; }
  function curType() { var c = f.querySelector('input[name=listing_type]:checked'); return c ? c.value : 'item'; }
  function setRadio(name, v) { var r = f.querySelector('input[name="' + name + '"][value="' + v + '"]'); if (r) r.checked = true; }

  function render() {
    [].slice.call(grid.querySelectorAll('.pf-th')).forEach(function (n) { n.remove(); });
    [].slice.call(dt.files).forEach(function (file, i) {
      var d = document.createElement('div'); d.className = 'pf-th';
      var im = document.createElement('img'); im.src = URL.createObjectURL(file); im.alt = '';
      im.addEventListener('click', function () { makeCover(i); });
      var x = document.createElement('button'); x.type = 'button'; x.className = 'pf-x'; x.setAttribute('aria-label', 'Remove photo'); x.textContent = '×';
      x.addEventListener('click', function () { removeAt(i); });
      d.appendChild(im); d.appendChild(x);
      if (i === 0) { var t = document.createElement('span'); t.className = 'pf-cv'; t.textContent = 'Cover'; d.appendChild(t); }
      grid.insertBefore(d, add);
    });
    cnt.textContent = dt.files.length + '/' + MAX;
    add.style.display = dt.files.length >= MAX ? 'none' : '';
  }
  function rebuild(arr) {
    dt = new DataTransfer();
    arr.forEach(function (x) { dt.items.add(x); });
    real.files = dt.files;
    render(); update();
  }
  function removeAt(i) { var a = [].slice.call(dt.files); a.splice(i, 1); rebuild(a); }
  function makeCover(i) { if (i === 0) return; var a = [].slice.call(dt.files); var c = a.splice(i, 1)[0]; a.unshift(c); rebuild(a); }

  pick.addEventListener('change', function () {
    var a = [].slice.call(dt.files), skipped = 0;
    [].slice.call(pick.files).forEach(function (file) {
      if (a.length >= MAX) return;
      if (file.size > LIM) { skipped++; return; }
      a.push(file);
    });
    pick.value = '';
    warn.textContent = skipped ? skipped + ' photo(s) skipped: each one must be under 4 MB.' : '';
    rebuild(a);
  });

  function applyType() {
    var t = curType();
    [].slice.call(f.querySelectorAll('[data-types]')).forEach(function (n) {
      n.hidden = n.getAttribute('data-types').split(',').indexOf(t) < 0;
    });
    if (t !== 'item') {
      setRadio('condition', 'N/A');
      if (t === 'service') setRadio('category', 'Services');
      if (t === 'rent') setRadio('category', 'Rentals');
    } else {
      var c = f.querySelector('input[name=condition]:checked');
      if (!c || c.value === 'N/A') setRadio('condition', 'New');
    }
    var ph = { item: 'e.g. Samsung Galaxy A14, 128GB, black', service: 'e.g. Professional tailoring and alterations', rent: 'e.g. Self-contained room near campus gate', store: 'e.g. Mama Blessing Provisions, all items' };
    document.getElementById('pfTitle').placeholder = ph[t];
  }

  function update() {
    var t = curType(), title = val('pfTitle'), price = parseFloat(val('pfPrice')) || 0, loc = val('pfLoc');
    var desc = val('pfDesc'), hi = val('pfHi'), brand = val('pfBrand'), wa = val('pfWa'), ph = val('pfPh'), n = dt.files.length;

    document.getElementById('pfTc').textContent = title.length + '/80';
    document.getElementById('pfDc').textContent = desc.length + ' characters';

    pvT.textContent = title || 'Your title appears here';
    pvP.textContent = '₦' + (price ? price.toLocaleString('en-NG') : '0');
    pvL.textContent = loc || 'Location';
    pvTag.textContent = { item: 'Item', service: 'Service', rent: 'For rent', store: 'Store' }[t];
    pvNeg.hidden = !document.getElementById('pfNeg').checked;

    var cov = n ? dt.files[0] : null;
    var key = cov ? cov.name + cov.size + cov.lastModified : '';
    if (key !== lastCov) {
      lastCov = key;
      pvI.innerHTML = '';
      if (cov) { var im = document.createElement('img'); im.src = URL.createObjectURL(cov); im.alt = ''; pvI.appendChild(im); }
      else { pvI.innerHTML = '<i data-lucide="image"></i>'; if (window.lucide) lucide.createIcons(); }
    }

    var s = 0, tips = [];
    if (n >= 1) s += 25; else tips.push('Add at least one photo');
    if (n >= 3) s += 10; else if (n >= 1) tips.push('Add 3 or more photos');
    if (title.length >= 12) s += 15; else tips.push('Write a clearer title');
    if (desc.length >= 60) s += 20; else tips.push('Describe it in more detail');
    if (price > 0) s += 10; else tips.push('Set a price');
    if (hi) s += 10; else tips.push('Add a few highlights');
    if (brand || t === 'service' || t === 'rent') s += 5;
    if (wa || ph) s += 5; else tips.push('Add a contact number');
    document.getElementById('pfBar').style.width = s + '%';
    document.getElementById('pfLab').textContent = s < 40 ? 'Needs more detail' : (s < 75 ? 'Good listing' : 'Excellent listing');
    document.getElementById('pfTip').textContent = tips.length ? tips[0] : 'Looks great. Ready to post.';
  }

  f.addEventListener('input', update);
  f.addEventListener('change', function (e) { if (e.target && e.target.name === 'listing_type') applyType(); update(); });
  f.addEventListener('submit', function () {
    var b = document.getElementById('pfSubmit');
    b.disabled = true;
    var sp = b.querySelector('span'); if (sp) sp.textContent = 'Posting...';
  });

  applyType(); render(); update();
  if (window.lucide) lucide.createIcons();
})();
</script>
@endsection
