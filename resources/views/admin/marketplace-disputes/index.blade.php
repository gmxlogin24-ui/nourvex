@extends('layouts.app-admin')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Marketplace Disputes</h1>
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
            <div class="stat-card">
                <div class="flex justify-between items-start gap-3 mb-3">
                    <div>
                        <a href="{{ route('marketplace.show', $dispute->listing_id) }}" class="text-accent font-semibold">{{ $dispute->listing->title }}</a>
                        <p class="text-muted text-xs">{{ ucfirst($dispute->listing->delivery_method) }} · filed by {{ $dispute->raisedBy->name ?? 'Unknown' }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <p class="font-semibold">₦{{ number_format($dispute->listing->amount, 2) }}</p>
                        <p class="text-xs text-muted">in escrow</p>
                    </div>
                </div>
                <p class="text-sm text-muted mb-3">{{ $dispute->description }}</p>
                <div class="flex justify-between items-center flex-wrap gap-2">
                    <p class="text-xs text-muted">{{ $dispute->created_at->diffForHumans() }}</p>
                    <div class="flex gap-2 flex-wrap">
                        <form method="POST" action="{{ route('admin.marketplace-disputes.pay', $dispute) }}" onsubmit="return confirm('Release ₦{{ number_format($dispute->listing->amount, 2) }} to the seller?');">
                            @csrf
                            <button class="balance-chip primary" style="font-size:0.75rem;">Pay Seller</button>
                        </form>
                        <form method="POST" action="{{ route('admin.marketplace-disputes.refund', $dispute) }}" onsubmit="return confirm('Refund ₦{{ number_format($dispute->listing->amount, 2) }} to the buyer?');">
                            @csrf
                            <button class="balance-chip" style="font-size:0.75rem;">Refund Buyer</button>
                        </form>
                        <form method="POST" action="{{ route('admin.marketplace-disputes.dismiss', $dispute) }}" onsubmit="return confirm('Dismiss this complaint? No action will be taken.');">
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
</div>
@endsection
