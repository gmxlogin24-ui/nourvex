@extends('layouts.app-authenticated')
@section('content')

<main class="max-w-xl mx-auto p-6">
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
