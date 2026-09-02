@extends('layouts.app-authenticated')

@section('title', 'LEVI — QLEVO Concierge')

@section('content')
<main class="w-full p-0 concierge-page">
    <div class="concierge-header">
        <a href="{{ route('dashboard') }}" class="concierge-back" aria-label="Back">
            <i data-lucide="chevron-left" class="w-5 h-5"></i>
        </a>
        <div class="concierge-orb"></div>
        <div class="concierge-header-text">
            <div class="concierge-title">LEVI</div>
            <div class="concierge-subtitle" id="concierge-status">always here</div>
        </div>
    </div>

    <div class="concierge-thread" id="concierge-thread">
        @forelse($messages as $m)
            @if($m->message_type === 'text')
                <div class="concierge-bubble {{ $m->is_ai ? 'ai' : 'user' }}">
                    @if($m->is_ai)<div class="concierge-avatar"></div>@endif
                    <div class="concierge-bubble-body">{{ $m->body }}</div>
                </div>
            @elseif($m->message_type === 'action_card')
                <div class="concierge-bubble ai">
                    <div class="concierge-avatar"></div>
                    <div class="concierge-action-card">
                        <div class="concierge-action-label">{{ str_replace('_', ' ', $m->meta['tool'] ?? 'action') }}</div>
                        <pre class="concierge-action-json">{{ json_encode($m->meta['result'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            @endif
        @empty
            <div class="concierge-empty">
                <div class="concierge-empty-orb"></div>
                <div class="concierge-empty-title">Ask, and it's handled</div>
                <div class="concierge-empty-desc">Your wallet, gigs, and marketplace — one line away.</div>
                <div class="concierge-suggestions">
                    <button class="concierge-chip" data-fill="Check my wallet balance">
                        <i data-lucide="wallet" class="w-3.5 h-3.5"></i> Check my wallet balance
                    </button>
                    <button class="concierge-chip" data-fill="Find gigs under 20,000 naira">
                        <i data-lucide="search" class="w-3.5 h-3.5"></i> Find gigs under ₦20,000
                    </button>
                    <button class="concierge-chip" data-fill="Claim today's Q Coin reward">
                        <i data-lucide="coins" class="w-3.5 h-3.5"></i> Claim today's Q Coin
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    <form id="concierge-form" class="concierge-composer">
        @csrf
        <input type="text" id="concierge-input" class="concierge-input" placeholder="Message LEVI…" autocomplete="off">
        <button type="submit" class="concierge-send" aria-label="Send">
            <i data-lucide="send" class="w-4 h-4"></i>
        </button>
    </form>
</main>
@endsection

@push('scripts')
<script src="{{ asset('js/qlevo-chat.js') }}?v={{ time() }}"></script>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    QLevoChat.initConcierge({
      sendUrl: '{{ route('concierge.message') }}',
      threadEl: document.getElementById('concierge-thread'),
      formEl: document.getElementById('concierge-form'),
      inputEl: document.getElementById('concierge-input'),
      statusEl: document.getElementById('concierge-status'),
    });
    if (window.lucide) lucide.createIcons();
  });
</script>
@endpush
