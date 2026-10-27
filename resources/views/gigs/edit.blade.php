@extends('layouts.world-shell')
@section('world', 'gigs')
@section('content')
<link rel="stylesheet" href="{{ asset('css/world-pages.css') }}?v={{ @filemtime(public_path('css/world-pages.css')) }}"><link rel="stylesheet" href="{{ asset('css/world-page.css') }}?v={{ @filemtime(public_path('css/world-page.css')) }}">
<main class="wp-page wp-gigs">
    <a href="{{ route('gigs.show', $gig->id) }}" class="text-muted text-sm flex items-center gap-1 mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Gig
    </a>

    <div class="glass-card p-8">
        <h1 class="text-xl font-semibold mb-1">Edit Gig</h1>
        <p class="text-muted text-sm mb-6">Changing the price adjusts your escrow automatically.</p>

        @if ($errors->any())
        <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('gigs.update', $gig->id) }}" class="space-y-4" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div>
                <label class="text-xs" style="opacity:0.7;">Title</label>
                <input type="text" name="title" required value="{{ old('title', $gig->title) }}" class="input-field">
            </div>
            <div>
                <label class="text-xs" style="opacity:0.7;">Category</label>
                <select name="category" id="gigCategoryEdit" required class="input-field">
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ old('category', $gig->category) === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div id="gigCategoryFieldsEdit" class="space-y-3"></div>
            <div>
                <label class="text-xs" style="opacity:0.7;">Description</label>
                <textarea name="description" rows="4" class="input-field">{{ old('description', $gig->description) }}</textarea>
            </div>
            <div>
                <label class="text-xs" style="opacity:0.7;">Amount (₦)</label>
                <input type="number" name="amount" id="gigAmountEdit" min="1" value="{{ old('amount', $gig->amount) }}" class="input-field">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="gigUsePackagesEdit" {{ (old('packages') || $gig->packages) ? 'checked' : '' }}>
                <label for="gigUsePackagesEdit" class="text-xs" style="opacity:0.7;">Offer multiple price packages instead of one price (up to 3)</label>
            </div>
            <div id="gigPackagesBuilderEdit" style="display:none;" class="space-y-2"></div>
            <div>
                <label class="text-xs" style="opacity:0.7;">WhatsApp (optional)</label>
                <input type="text" name="whatsapp" value="{{ old('whatsapp', $gig->whatsapp) }}" class="input-field">
            </div>
            <div>
                <label class="text-xs" style="opacity:0.7;">Call Number (optional)</label>
                <input type="text" name="phone" value="{{ old('phone', $gig->phone) }}" class="input-field">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-xs" style="opacity:0.7;">Deadline (optional)</label>
                    <input type="date" name="deadline" value="{{ old('deadline', optional($gig->deadline)->format('Y-m-d')) }}" class="input-field">
                </div>
                <div>
                    <label class="text-xs" style="opacity:0.7;">Location type</label>
                    <select name="location_type" class="input-field">
                        <option value="">— Select —</option>
                        <option value="remote" {{ old('location_type', $gig->location_type) === 'remote' ? 'selected' : '' }}>Remote</option>
                        <option value="onsite" {{ old('location_type', $gig->location_type) === 'onsite' ? 'selected' : '' }}>On-site</option>
                        <option value="pickup" {{ old('location_type', $gig->location_type) === 'pickup' ? 'selected' : '' }}>Pickup</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="text-xs" style="opacity:0.7;">Location (optional)</label>
                <input type="text" name="location" value="{{ old('location', $gig->location) }}" class="input-field">
            </div>
            <div>
                <label class="text-xs" style="opacity:0.7;">Replace attached file (optional, max 2MB)</label>
                @if($gig->attachment_path)
                <p class="text-xs text-muted mb-1">Current: <a href="/storage/{{ $gig->attachment_path }}" target="_blank" class="text-accent">view file</a></p>
                @endif
                <input type="file" name="attachment" class="input-field">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="has_item" id="gigHasItemEdit" value="1" {{ old('has_item', $gig->has_item) ? 'checked' : '' }}>
                <label for="gigHasItemEdit" class="text-xs" style="opacity:0.7;">This gig involves delivering a physical item</label>
            </div>
            <div id="gigItemFieldsEdit" class="space-y-3" style="display:none;">
                <div>
                    <label class="text-xs" style="opacity:0.7;">Item name</label>
                    <input type="text" name="item_name" value="{{ old('item_name', $gig->item_name) }}" class="input-field">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs" style="opacity:0.7;">Quantity</label>
                        <input type="number" name="item_qty" min="1" value="{{ old('item_qty', $gig->item_qty) }}" class="input-field">
                    </div>
                    <div>
                        <label class="text-xs" style="opacity:0.7;">Item cost (₦, informational)</label>
                        <input type="number" name="item_cost" min="0" value="{{ old('item_cost', $gig->item_cost) }}" class="input-field">
                    </div>
                </div>
                <div>
                    <label class="text-xs" style="opacity:0.7;">Delivery address</label>
                    <input type="text" name="delivery_address" value="{{ old('delivery_address', $gig->delivery_address) }}" class="input-field">
                </div>
                <div>
                    <label class="text-xs" style="opacity:0.7;">Delivery method</label>
                    <select name="item_delivery_method" class="input-field">
                        <option value="">— Select —</option>
                        <option value="meetup" {{ old('item_delivery_method', $gig->item_delivery_method) === 'meetup' ? 'selected' : '' }}>Meet on campus</option>
                        <option value="ship" {{ old('item_delivery_method', $gig->item_delivery_method) === 'ship' ? 'selected' : '' }}>Ship / send</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-primary w-full justify-center">Save Changes</button>
        </form>
    </div>
</main>
<script>
const gigCategoryFieldsEdit = @json(config('gig_categories'));
const gigOldPackagesEdit = @json(old('packages', $gig->packages ?? []));

function gigPackageRowEdit(index, pkg) {
    pkg = pkg || {};
    const wrap = document.createElement('div');
    wrap.className = 'glass-card p-3 space-y-2';
    wrap.innerHTML =
        '<input type="text" name="packages[' + index + '][name]" placeholder="Tier name (e.g. Basic)" class="input-field" value="' + (pkg.name || '') + '">' +
        '<input type="number" name="packages[' + index + '][price]" min="1" placeholder="Price (₦)" class="input-field" value="' + (pkg.price || '') + '">' +
        '<input type="text" name="packages[' + index + '][description]" placeholder="What\'s included (optional)" class="input-field" value="' + (pkg.description || '') + '">';
    return wrap;
}

function gigInitPackagesBuilderEdit(checkboxId, containerId, amountId) {
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
                container.appendChild(gigPackageRowEdit(i, gigOldPackagesEdit[i]));
            }
        }
        refreshAmountRequired();
    }

    checkbox.addEventListener('change', toggle);
    toggle();
}
const gigExistingDetails = @json($gig->details ?? []);
function renderGigCategoryFieldsEdit(selected) {
    const container = document.getElementById('gigCategoryFieldsEdit');
    if (!container) return;
    container.innerHTML = '';
    const fields = (gigCategoryFieldsEdit[selected] && gigCategoryFieldsEdit[selected].fields) || [];
    fields.forEach(function (f) {
        const wrap = document.createElement('div');
        const label = document.createElement('label');
        label.className = 'text-xs';
        label.style.opacity = '0.7';
        label.textContent = f.label;
        wrap.appendChild(label);
        let input;
        if (f.type === 'select' && f.options) {
            input = document.createElement('select');
            input.className = 'input-field';
            const blank = document.createElement('option');
            blank.value = '';
            blank.textContent = '— Select —';
            input.appendChild(blank);
            f.options.forEach(function (opt) {
                const o = document.createElement('option');
                o.value = opt;
                o.textContent = opt;
                if (gigExistingDetails[f.key] === opt) o.selected = true;
                input.appendChild(o);
            });
        } else {
            input = document.createElement('input');
            input.type = 'text';
            input.className = 'input-field';
            if (gigExistingDetails[f.key]) input.value = gigExistingDetails[f.key];
        }
        input.name = 'details[' + f.key + ']';
        wrap.appendChild(input);
        container.appendChild(wrap);
    });
}
document.addEventListener('DOMContentLoaded', function () {
    const sel = document.getElementById('gigCategoryEdit');
    if (sel) {
        renderGigCategoryFieldsEdit(sel.value);
        sel.addEventListener('change', function () { renderGigCategoryFieldsEdit(sel.value); });
    }
    const hasItem = document.getElementById('gigHasItemEdit');
    const itemFields = document.getElementById('gigItemFieldsEdit');
    if (hasItem && itemFields) {
        const toggle = function () { itemFields.style.display = hasItem.checked ? 'block' : 'none'; };
        toggle();
        hasItem.addEventListener('change', toggle);
    }
    gigInitPackagesBuilderEdit('gigUsePackagesEdit', 'gigPackagesBuilderEdit', 'gigAmountEdit');
});
</script>
@endsection
