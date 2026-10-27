@extends('layouts.world-shell')
@section('world', 'market')
@section('content')
<link rel="stylesheet" href="{{ asset('css/world-pages.css') }}?v={{ @filemtime(public_path('css/world-pages.css')) }}"><link rel="stylesheet" href="{{ asset('css/world-page.css') }}?v={{ @filemtime(public_path('css/world-page.css')) }}">

<main class="wp-page wp-market">
    <a href="{{ route('marketplace.show', $listing->id) }}" class="text-muted text-sm flex items-center gap-1 mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Listing
    </a>

    <div class="glass-card p-6">
        <h1 class="text-xl font-semibold mb-1">Edit Listing</h1>
        <p class="text-muted text-sm mb-6">Update your listing details</p>

        @if ($errors->any())
        <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('marketplace.update', $listing->id) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="text-sm text-muted">What are you posting?</label>
                <select name="listing_type" required class="input-field !mt-1" id="listingType">
                    <option value="item" @selected($listing->listing_type === 'item')>Item for Sale</option>
                    <option value="service" @selected($listing->listing_type === 'service')>Service / Handiwork (tailoring, repairs, tutoring, etc.)</option>
                    <option value="rent" @selected($listing->listing_type === 'rent')>For Rent (room, gadget, etc.)</option>
                    <option value="store" @selected($listing->listing_type === 'store')>My Store (shop with multiple products)</option>
                </select>
            </div>
            <div id="storeNameField" style="{{ $listing->listing_type === 'store' ? '' : 'display:none;' }}">
                <label class="text-sm text-muted">Store Name</label>
                <input type="text" name="store_name" value="{{ $listing->store_name }}" placeholder="e.g. Mama Blessing Provisions" class="input-field !mt-1">
            </div>
            <div>
                <label class="text-sm text-muted">Photos (up to 6)</label>
                @if($listing->image_url)
                <img src="{{ $listing->image_url }}" class="w-20 h-20 object-cover rounded-lg mb-2" alt="Current photo">
                <p class="text-muted text-xs mb-2">Current photo — upload a new one to replace it</p>
                @endif
                @php $emImgs = $listing->images()->get(); @endphp
                @if($emImgs->count())
                <style>.em-photos{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin:6px 0}.em-ph{position:relative;border-radius:12px;overflow:hidden;border:1px solid rgba(128,128,128,.3)}.em-ph img{width:100%;aspect-ratio:1/1;object-fit:cover;display:block}.em-ph label{display:flex;align-items:center;gap:5px;padding:5px 7px;font-size:11.5px;font-weight:700}.em-hint{font-size:11.5px;opacity:.65;margin:2px 0 8px}</style>
                <div class="em-photos">
                  @foreach($emImgs as $emI)
                    <div class="em-ph"><img src="{{ $emI->image_url }}" alt=""><label><input type="radio" name="cover_image" value="{{ $emI->id }}"> Make cover</label><label><input type="checkbox" name="remove_images[]" value="{{ $emI->id }}"> Remove</label></div>
                  @endforeach
                </div>
                <p class="em-hint">Tick Make cover to set the main photo, or Remove to delete one. Add more below: front, side, top and back.</p>
                @endif
                <input type="file" name="images[]" accept="image/*" multiple class="input-field !mt-1">
            </div>
            <div>
                <label class="text-sm text-muted">Title</label>
                <input type="text" name="title" required value="{{ $listing->title }}" placeholder="e.g. Blue denim jacket, size M" class="input-field !mt-1">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm text-muted">Category</label>
                    <select name="category" required class="input-field !mt-1">
                        @foreach(['Fashion', 'Electronics', 'Books', 'Food', 'Services', 'Rentals', 'Other'] as $cat)
                        <option @selected($listing->category === $cat)>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm text-muted">Condition</label>
                    <select name="condition" required class="input-field !mt-1">
                        @foreach(['New', 'Like New', 'Used', 'N/A'] as $cond)
                        <option @selected($listing->condition === $cond)>{{ $cond }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @if(in_array(auth()->user()->tier, ['tier2', 'tier3'], true))
            <div>
                <label class="text-sm text-muted">Stock Quantity (optional — leave blank for a single one-off item)</label>
                <input type="number" name="stock_quantity" min="1" value="{{ $listing->stock_quantity ?? '' }}" placeholder="e.g. 10" class="input-field !mt-1">
            </div>
            @endif
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm text-muted">Price (₦)</label>
                    <input type="number" name="amount" required min="0" value="{{ $listing->amount }}" class="input-field !mt-1">
                </div>
                <div>
                    <label class="text-sm text-muted">Location</label>
                    <input type="text" name="location" required value="{{ $listing->location }}" placeholder="e.g. DELSU Abraka" class="input-field !mt-1">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm text-muted">WhatsApp Number</label>
                    <input type="text" name="whatsapp" value="{{ $listing->whatsapp }}" placeholder="e.g. 08012345678" class="input-field !mt-1">
                </div>
                <div>
                    <label class="text-sm text-muted">Call Number</label>
                    <input type="text" name="phone" value="{{ $listing->phone }}" placeholder="e.g. 08012345678" class="input-field !mt-1">
                </div>
            </div>
            <div>
                <label class="text-sm text-muted">Description</label>
                <textarea name="description" rows="3" class="input-field !mt-1">{{ $listing->description }}</textarea>
<div class="lf">
  <style>.lf{display:grid;gap:6px;margin-top:12px}.lf label{font-size:12.5px;font-weight:700}.lf small{font-weight:500;opacity:.65}.lf-ck{display:flex;align-items:center;gap:8px}</style>
  <label>Brand <small>(optional)</small></label>
  <input type="text" name="brand" maxlength="100" value="{{ old('brand', $listing->brand) }}" placeholder="e.g. Samsung" class="input-field !mt-1">
  <label>Highlights <small>(one per line)</small></label>
  <textarea name="highlights" rows="3" maxlength="1500" placeholder="Battery lasts 2 days" class="input-field !mt-1">{{ old('highlights', $listing->highlights) }}</textarea>
  <label>What's in the box <small>(one per line)</small></label>
  <textarea name="whats_in_box" rows="2" maxlength="1000" placeholder="Charger" class="input-field !mt-1">{{ old('whats_in_box', $listing->whats_in_box) }}</textarea>
  <label>Warranty <small>(optional)</small></label>
  <input type="text" name="warranty" maxlength="100" value="{{ old('warranty', $listing->warranty) }}" placeholder="e.g. 3 months" class="input-field !mt-1">
  <label>Return policy <small>(optional)</small></label>
  <textarea name="return_policy" rows="2" maxlength="1000" placeholder="e.g. Returns accepted within 2 days if faulty" class="input-field !mt-1">{{ old('return_policy', $listing->return_policy) }}</textarea>
  <label class="lf-ck"><input type="checkbox" name="negotiable" value="1" {{ old('negotiable', $listing->negotiable) ? 'checked' : '' }}> Price is negotiable</label>
</div>
            </div>
            <div>
                <label class="text-sm text-muted">Delivery Method</label>
                <select name="delivery_method" required class="input-field !mt-1">
                    <option value="meetup" @selected($listing->delivery_method === 'meetup')>Meet on campus</option>
                    <option value="ship" @selected($listing->delivery_method === 'ship')>I'll ship it (buyer may not be nearby)</option>
                </select>
            </div>
            <button type="submit" class="btn-primary w-full justify-center">Save Changes</button>
        </form>
    </div>
</main>

<script>
document.getElementById('listingType').addEventListener('change', function() {
    document.getElementById('storeNameField').style.display = this.value === 'store' ? 'block' : 'none';
});
</script>
@endsection
