@extends('layouts.app-admin')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Gig Disputes</h1>
        <a href="{{ route('admin.index') }}" class="text-accent text-sm">← Back</a>
    </div>

    @if(session('status'))
    <div class="stat-card mb-4" style="border-color:var(--color-accent);"><p>{{ session('status') }}</p></div>
    @endif
    @if($errors->any())
    <div class="stat-card mb-4" style="border-color:#ef4444;"><p>{{ $errors->first() }}</p></div>
    @endif

    @if($disputes->count())
    <div class="flex flex-col gap-3">
        @foreach($disputes as $dispute)
            @php $remaining = $dispute->gig->amount - $dispute->gig->escrowed_amount; @endphp
            <div class="stat-card" data-lazy-card="lazy-wrap-gig" data-dispute-id="{{ $dispute->id }}">
                <div class="flex justify-between items-start gap-3 mb-3">
                    <div>
                        <a href="{{ route('gigs.show', $dispute->gig_id) }}" class="text-accent font-semibold">{{ $dispute->gig->title }}</a>
                        <p class="text-muted text-xs">₦{{ number_format($dispute->gig->amount) }} total · filed by {{ $dispute->raisedBy->name ?? 'Unknown' }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="font-semibold">₦{{ number_format($remaining, 2) }}</p>
                        <p class="text-xs text-muted">owed</p>
                    </div>
                </div>
                <p class="text-sm text-muted mb-3">{{ $dispute->description }}</p>
                <div class="flex justify-between items-center flex-wrap gap-2">
                    <p class="text-xs text-muted">{{ $dispute->created_at->diffForHumans() }}</p>
                    <div class="flex gap-2 flex-wrap">
                        <form method="POST" action="{{ route('admin.disputes.pay', $dispute) }}" onsubmit="return confirm('Force-pay the worker ₦{{ number_format($remaining, 2) }}? The poster\'s wallet will be charged even if it goes negative.');">
                            @csrf
                            <button class="balance-chip primary" style="font-size:0.75rem;">Pay Worker</button>
                        </form>
                        <form method="POST" action="{{ route('admin.disputes.partial', $dispute) }}" onsubmit="return confirm('Resolve as partial? Worker gets that % of the gig amount, poster is charged or refunded to match.');" class="flex items-center gap-1">
                            @csrf
                            <input type="number" name="worker_percent" min="0" max="100" step="1" required placeholder="%" class="input-field" style="width:60px;padding:0.3rem 0.4rem;font-size:0.75rem;">
                            <button class="balance-chip" style="font-size:0.75rem;">Partial</button>
                        </form>
                        <form method="POST" action="{{ route('admin.disputes.dismiss', $dispute) }}" onsubmit="return confirm('Dismiss this complaint? No payment will be made.');">
                            @csrf
                            <button class="balance-chip" style="font-size:0.75rem;">Dismiss</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    @else
    <div class="stat-card text-center text-muted py-8">No open disputes.</div>
    @endif

    @include('partials.lazy-mode-bar', ['ccAgents' => $ccAgents, 'assignType' => 'gig'])
</div>
@endsection
