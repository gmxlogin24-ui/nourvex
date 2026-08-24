@extends('layouts.app-admin')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Gig Thread: {{ $gig->title }}</h1>
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

    <form method="POST" action="{{ route('customer-care.gigs.message', $gig) }}" class="flex gap-2">
        @csrf
        <select name="recipient_id" required class="glass-input" style="width:160px;">
            <option value="{{ $gig->user_id }}">Poster</option>
            @if($gig->accepted_by)<option value="{{ $gig->accepted_by }}">Worker</option>@endif
        </select>
        <input type="text" name="body" required placeholder="Message" class="glass-input" style="flex:1;">
        <button class="balance-chip primary" style="font-size:0.75rem;">Send</button>
    </form>
</div>
@endsection
