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

    <div class="stat-card overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-muted border-b" style="border-color:var(--border-color);">
                    <th class="py-2 pr-4">Gig</th>
                    <th class="py-2 pr-4">Filed By</th>
                    <th class="py-2 pr-4">Amount Owed</th>
                    <th class="py-2 pr-4">Description</th>
                    <th class="py-2 pr-4">Filed</th>
                    <th class="py-2 pr-4">Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($disputes as $dispute)
                @php $remaining = $dispute->gig->amount - $dispute->gig->escrowed_amount; @endphp
                <tr class="border-b" style="border-color:var(--border-color);">
                    <td class="py-2 pr-4">
                        <a href="{{ route('gigs.show', $dispute->gig_id) }}" class="text-accent">{{ $dispute->gig->title }}</a>
                        <p class="text-muted text-xs">₦{{ number_format($dispute->gig->amount) }} total</p>
                    </td>
                    <td class="py-2 pr-4">{{ $dispute->raisedBy->name ?? 'Unknown' }}</td>
                    <td class="py-2 pr-4">₦{{ number_format($remaining, 2) }}</td>
                    <td class="py-2 pr-4 text-muted" style="max-width:280px;">{{ $dispute->description }}</td>
                    <td class="py-2 pr-4 text-muted">{{ $dispute->created_at->diffForHumans() }}</td>
                    <td class="py-2 pr-4 flex gap-2 flex-wrap">
                        <form method="POST" action="{{ route('admin.disputes.pay', $dispute) }}" onsubmit="return confirm('Force-pay the worker ₦{{ number_format($remaining, 2) }}? The poster\'s wallet will be charged even if it goes negative.');">
                            @csrf
                            <button class="balance-chip primary" style="font-size:0.75rem;">Pay Worker</button>
                        </form>
                        <form method="POST" action="{{ route('admin.disputes.dismiss', $dispute) }}" onsubmit="return confirm('Dismiss this complaint? No payment will be made.');">
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
