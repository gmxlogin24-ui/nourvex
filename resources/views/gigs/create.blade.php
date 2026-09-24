@extends('layouts.app-authenticated')
@section('content')


<main class="max-w-xl mx-auto p-6">
    <a href="{{ route('gigs.index') }}" class="text-muted text-sm flex items-center gap-1 mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Gigs
    </a>

    <div class="glass-card p-6">
        <h1 class="text-xl font-semibold mb-1">Post a Gig</h1>
        <p class="text-muted text-sm mb-6">Need something done? Post it here — the amount is held from your wallet until someone completes it.</p>

        @if ($errors->any())
        <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('gigs.store') }}" class="space-y-4" enctype="multipart/form-data">
            @csrf
            <div>
                <label class="text-sm text-muted">Title</label>
                <input type="text" name="title" required value="{{ old('title') }}" placeholder="e.g. Type and bind my 40-page project" class="input-field !mt-1">
            </div>
            <div>
                <label class="text-sm text-muted">Category</label>
                <select name="category" id="gigCategory" required class="input-field !mt-1">
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>
            <div id="gigCategoryFields" class="space-y-3"></div>
            <div>
                <label class="text-sm text-muted">Amount (₦)</label>
                <input type="number" name="amount" id="gigAmount" min="1" value="{{ old('amount') }}" class="input-field !mt-1">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="gigUsePackages" {{ old('packages') ? 'checked' : '' }}>
                <label for="gigUsePackages" class="text-sm text-muted">Offer multiple price packages instead of one price (up to 3)</label>
            </div>
            <div id="gigPackagesBuilder" style="display:none;" class="space-y-2"></div>
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
                <textarea name="description" rows="3" class="input-field !mt-1">{{ old('description') }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="text-sm text-muted">Deadline (optional)</label>
                    <input type="date" name="deadline" value="{{ old('deadline') }}" class="input-field !mt-1">
                </div>
                <div>
                    <label class="text-sm text-muted">Location type</label>
                    <select name="location_type" class="input-field !mt-1">
                        <option value="">— Select —</option>
                        <option value="remote" {{ old('location_type') === 'remote' ? 'selected' : '' }}>Remote</option>
                        <option value="onsite" {{ old('location_type') === 'onsite' ? 'selected' : '' }}>On-site</option>
                        <option value="pickup" {{ old('location_type') === 'pickup' ? 'selected' : '' }}>Pickup</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="text-sm text-muted">Location (optional)</label>
                <input type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Hostel B, or leave blank if remote" class="input-field !mt-1">
            </div>
            <div>
                <label class="text-sm text-muted">Attach a brief or reference file (optional, max 2MB)</label>
                <input type="file" name="attachment" class="input-field !mt-1">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="has_item" id="gigHasItem" value="1" {{ old('has_item') ? 'checked' : '' }}>
                <label for="gigHasItem" class="text-sm text-muted">This gig involves delivering a physical item</label>
            </div>
            <div id="gigItemFields" class="space-y-3" style="display:none;">
                <div>
                    <label class="text-sm text-muted">Item name</label>
                    <input type="text" name="item_name" value="{{ old('item_name') }}" class="input-field !mt-1">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm text-muted">Quantity</label>
                        <input type="number" name="item_qty" min="1" value="{{ old('item_qty') }}" class="input-field !mt-1">
                    </div>
                    <div>
                        <label class="text-sm text-muted">Item cost (₦, informational)</label>
                        <input type="number" name="item_cost" min="0" value="{{ old('item_cost') }}" class="input-field !mt-1">
                    </div>
                </div>
                <div>
                    <label class="text-sm text-muted">Delivery address</label>
                    <input type="text" name="delivery_address" value="{{ old('delivery_address') }}" class="input-field !mt-1">
                </div>
                <div>
                    <label class="text-sm text-muted">Delivery method</label>
                    <select name="item_delivery_method" class="input-field !mt-1">
                        <option value="">— Select —</option>
                        <option value="meetup" {{ old('item_delivery_method') === 'meetup' ? 'selected' : '' }}>Meet on campus</option>
                        <option value="ship" {{ old('item_delivery_method') === 'ship' ? 'selected' : '' }}>Ship / send</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-primary w-full justify-center" onclick="return confirm('Post this gig? 50% of the amount (or the lowest package price) will be held from your wallet immediately.');">Post Gig</button>
        </form>
    </div>
</main>
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
