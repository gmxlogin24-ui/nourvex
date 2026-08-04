@extends('layouts.app-authenticated')
@section('content')
<main class="max-w-2xl mx-auto p-6">
    <a href="{{ route('gigs.show', $gig->id) }}" class="text-muted text-sm flex items-center gap-1 mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Gig
    </a>

    <div class="glass-card p-8">
        <h1 class="text-xl font-semibold mb-1">Edit Gig</h1>
        <p class="text-muted text-sm mb-6">Changing the price adjusts your escrow automatically.</p>

        @if ($errors->any())
        <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('gigs.update', $gig->id) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="text-xs" style="opacity:0.7;">Title</label>
                <input type="text" name="title" required value="{{ old('title', $gig->title) }}" class="input-field">
            </div>
            <div>
                <label class="text-xs" style="opacity:0.7;">Category</label>
                <input type="text" name="category" required value="{{ old('category', $gig->category) }}" class="input-field">
            </div>
            <div>
                <label class="text-xs" style="opacity:0.7;">Description</label>
                <textarea name="description" rows="4" class="input-field">{{ old('description', $gig->description) }}</textarea>
            </div>
            <div>
                <label class="text-xs" style="opacity:0.7;">Amount (₦)</label>
                <input type="number" name="amount" required min="1" value="{{ old('amount', $gig->amount) }}" class="input-field">
            </div>
            <div>
                <label class="text-xs" style="opacity:0.7;">WhatsApp (optional)</label>
                <input type="text" name="whatsapp" value="{{ old('whatsapp', $gig->whatsapp) }}" class="input-field">
            </div>
            <button type="submit" class="btn-primary w-full justify-center">Save Changes</button>
        </form>
    </div>
</main>
@endsection
