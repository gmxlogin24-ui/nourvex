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

    <div class="stat-card overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-muted border-b" style="border-color:var(--border-color);">
                    <th class="py-2 pr-4">Listing</th>
                    <th class="py-2 pr-4">Filed By</th>
                    <th class="py-2 pr-4">Amount in Escrow</th>
                    <th class="py-2 pr-4">Description</th>
                    <th class="py-2 pr-4">Filed</th>
                    <th class="py-2 pr-4">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($disputes as $dispute)
                <tr class="border-b" style="border-color:var(--border-color);">
                    <td class="py-2 pr-4">
                        <a href="{{ route('marketplace.show', $dispute->listing_id) }}" class="text-accent">{{ $dispute->listing->title }}</a>
                        <p class="text-muted text-xs">{{ ucfirst($dispute->listing->delivery_method) }}</p>
                    </td>
                    <td class="py-2 pr-4">{{ $dispute->raisedBy->name ?? 'Unknown' }}</td>
                    <td class="py-2 pr-4">₦{{ number_format($dispute->listing->amount, 2) }}</td>
                    <td class="py-2 pr-4 text-muted" style="max-width:280px;">{{ $dispute->description }}</td>
                    <td class="py-2 pr-4 text-muted">{{ $dispute->created_at->diffForHumans() }}</td>
                    <td class="py-2 pr-4 flex gap-2 flex-wrap">
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
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="py-4 text-center text-muted">No open disputes.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
