@extends('layouts.app-authenticated')
@section('content')


<main class="max-w-xl mx-auto p-6">
    <a href="{{ route('marketplace.index') }}" class="text-muted text-sm flex items-center gap-1 mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Marketplace
    </a>

    <div class="glass-card p-6">
        <h1 class="text-xl font-semibold mb-1">Post to Marketplace</h1>
        <p class="text-muted text-sm mb-6">Sell an item, offer a service, list a rental, or run your store</p>

        @if ($errors->any())
        <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('marketplace.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm text-muted">What are you posting?</label>
                <select name="listing_type" required class="input-field !mt-1" id="listingType">
                    <option value="item">Item for Sale</option>
                    <option value="service">Service / Handiwork (tailoring, repairs, tutoring, etc.)</option>
                    <option value="rent">For Rent (room, gadget, etc.)</option>
                    <option value="store">My Store (shop with multiple products)</option>
                </select>
            </div>
            <div id="storeNameField" style="display:none;">
                <label class="text-sm text-muted">Store Name</label>
                <input type="text" name="store_name" placeholder="e.g. Mama Blessing Provisions" class="input-field !mt-1">
            </div>
            <div>
                <label class="text-sm text-muted">Photo</label>
                <input type="file" name="image" accept="image/*" class="input-field !mt-1">
            </div>
            <div>
                <label class="text-sm text-muted">Title</label>
                <input type="text" name="title" required placeholder="e.g. Blue denim jacket, size M" class="input-field !mt-1">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm text-muted">Category</label>
                    <select name="category" required class="input-field !mt-1">
                        <option>Fashion</option>
                        <option>Electronics</option>
                        <option>Books</option>
                        <option>Food</option>
                        <option>Services</option>
                        <option>Rentals</option>
                        <option>Other</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm text-muted">Condition</label>
                    <select name="condition" required class="input-field !mt-1">
                        <option>New</option>
                        <option>Like New</option>
                        <option>Used</option>
                        <option>N/A</option>
                    </select>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm text-muted">Price (₦)</label>
                    <input type="number" name="amount" required min="0" class="input-field !mt-1">
                </div>
                <div>
                    <label class="text-sm text-muted">Location</label>
                    <input type="text" name="location" required placeholder="e.g. DELSU Abraka" class="input-field !mt-1">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm text-muted">WhatsApp Number</label>
                    <input type="text" name="whatsapp" placeholder="e.g. 08012345678" class="input-field !mt-1">
                </div>
                <div>
                    <label class="text-sm text-muted">Call Number</label>
                    <input type="text" name="phone" placeholder="e.g. 08012345678" class="input-field !mt-1">
                </div>
            </div>
            <div>
                <label class="text-sm text-muted">Description</label>
                <textarea name="description" rows="3" class="input-field !mt-1"></textarea>
            </div>
            <button type="submit" class="btn-primary w-full justify-center">Post</button>
        </form>
    </div>
</main>

<script>
document.getElementById('listingType').addEventListener('change', function() {
    document.getElementById('storeNameField').style.display = this.value === 'store' ? 'block' : 'none';
});
</script>
@endsection
