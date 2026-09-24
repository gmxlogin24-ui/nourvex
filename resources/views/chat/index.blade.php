@extends('layouts.app-authenticated')
@section('content')
@if((auth()->user()->theme_style ?? 'glass') === 'plain')
@include('chat.partials.chat-minimal')
@else
<main class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-4 flex items-center gap-2"><i data-lucide="message-circle" class="w-5 h-5"></i> Messages</h1>

    @forelse($conversations as $c)
    <a href="{{ $c['link'] }}" class="glass-card p-4 mb-3 flex items-center gap-3 reveal-on-scroll" style="text-decoration:none;color:inherit;">
        <div class="w-12 h-12 rounded-full bg-card flex items-center justify-center font-semibold overflow-hidden flex-shrink-0">
            @if($c['other_avatar'])<img src="{{ $c['other_avatar'] }}" class="w-full h-full object-cover">@else{{ strtoupper(substr($c['other_name'], 0, 1)) }}@endif
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex justify-between items-center">
                <p class="font-medium text-sm">{{ $c['other_name'] }}</p>
                @if($c['last_at'])<p class="text-xs text-muted">{{ $c['last_at']->diffForHumans() }}</p>@endif
            </div>
            <p class="text-xs text-muted mt-0.5">{{ $c['subject_type'] }} #{{ $c['subject_id'] }}</p>
            <p class="text-sm text-muted mt-1 truncate">{{ $c['last_body'] }}</p>
        </div>
        @if($c['unread'] > 0)
        <span class="flex-shrink-0" style="background:var(--color-accent);color:#000;font-size:0.7rem;font-weight:600;padding:0.15rem 0.5rem;border-radius:999px;">{{ $c['unread'] }}</span>
        @endif
    </a>
    @empty
    <p class="text-muted text-sm text-center mt-8">No conversations yet.</p>
    @endforelse
</main>
@endif
@endsection
