@extends('layouts.app-admin')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Marketplace Disputes</h1>
        <a href="{{ route('customer-care.index') }}" class="text-accent text-sm">← Back</a>
    </div>
    @if(session('status'))<div class="stat-card mb-4" style="border-color:var(--color-accent);"><p>{{ session('status') }}</p></div>@endif

    @if($disputes->count())
    <div class="flex flex-col gap-3">
        @foreach($disputes as $d)
        <div class="stat-card">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="font-semibold">{{ $d->listing->title ?? 'Listing #' . $d->listing_id }}</p>
                    <p class="text-xs text-muted">Raised by {{ $d->raisedBy->name ?? '—' }} · {{ $d->status }}{{ $d->escalated ? ' · escalated' : '' }}</p>
                </div>
            </div>
            <p class="text-sm mb-3">{{ $d->description }}</p>
            @if($d->cc_notes)
            <div class="text-xs bg-black/5 rounded-lg p-2 mb-3 whitespace-pre-line">{{ $d->cc_notes }}</div>
            @endif
            <form method="POST" action="{{ route('customer-care.listing-disputes.note', $d) }}" class="flex gap-2 mb-2">
                @csrf
                <input type="text" name="note" required placeholder="Add internal note" class="glass-input" style="flex:1;">
                <button class="balance-chip primary" style="font-size:0.75rem;">Add Note</button>
            </form>
            @if(!$d->escalated)
            <form method="POST" action="{{ route('customer-care.listing-disputes.escalate', $d) }}">
                @csrf
                <button class="balance-chip" style="font-size:0.75rem;">Escalate to Admin</button>
            </form>
            @endif
        </div>
        @endforeach
    </div>
    @else
    <div class="stat-card text-center text-muted py-8">No open disputes.</div>
    @endif

    <div class="mt-4">{{ $disputes->links() }}</div>
</div>
@endsection
