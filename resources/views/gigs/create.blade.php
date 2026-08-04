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

        <form method="POST" action="{{ route('gigs.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm text-muted">Title</label>
                <input type="text" name="title" required value="{{ old('title') }}" placeholder="e.g. Type and bind my 40-page project" class="input-field !mt-1">
            </div>
            <div>
                <label class="text-sm text-muted">Category</label>
                <select name="category" required class="input-field !mt-1">
                    <option>Tutoring</option>
                    <option>Typing & Printing</option>
                    <option>Hair & Beauty</option>
                    <option>Repairs</option>
                    <option>Laundry</option>
                    <option>Errands & Delivery</option>
                    <option>Photography</option>
                    <option>Cash Agent (POS)</option>
                    <option>Other</option>
                </select>
            </div>
            <div>
                <label class="text-sm text-muted">Amount (₦)</label>
                <input type="number" name="amount" required min="1" value="{{ old('amount') }}" class="input-field !mt-1">
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
                <textarea name="description" rows="3" class="input-field !mt-1">{{ old('description') }}</textarea>
            </div>
            <button type="submit" class="btn-primary w-full justify-center">Post Gig</button>
        </form>
    </div>
</main>
@endsection
