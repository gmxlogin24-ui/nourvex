@extends('layouts.app-admin')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Listing Thread: {{ $listing->title }}</h1>
        <a href="{{ route('customer-care.index') }}" class="text-accent text-sm">← Back</a>
    </div>
    @if(session('status'))<div class="stat-card mb-4" style="border-color:var(--color-accent);"><p>{{ session('status') }}</p></div>@endif

    <div class="stat-card mb-4" style="max-height:400px;overflow-y:auto;">
        @foreach($messages as $m)
        <div class="text-sm py-2 border-b" style="border-color:var(--border-color);">
            <span class="font-medium">{{ $m->sender->name ?? '—' }}:</span> {{ $m->body }}
        </div>
        @endforeach
    </div>

    <form method="POST" action="{{ route('customer-care.listings.message', $listing) }}" class="flex gap-2">
        @csrf
        <input type="number" name="recipient_id" required placeholder="Recipient user ID" class="glass-input" style="width:140px;">
        <input type="text" name="body" required placeholder="Message" class="glass-input" style="flex:1;">
        <button class="balance-chip primary" style="font-size:0.75rem;">Send</button>
    </form>
</div>
@endsection
