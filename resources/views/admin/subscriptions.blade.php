@extends('layouts.app-admin')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Subscriptions</h1>
        <a href="{{ route('admin.index') }}" class="text-accent text-sm">← Back</a>
    </div>

    @if(session('status'))
    <div class="stat-card mb-4" style="border-color:var(--color-accent);"><p>{{ session('status') }}</p></div>
    @endif

    <div class="stat-card overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-muted border-b" style="border-color:var(--border-color);">
                    <th class="py-2 pr-4">User</th>
                    <th class="py-2 pr-4">Amount</th>
                    <th class="py-2 pr-4">Method</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4">Expires</th>
                    <th class="py-2 pr-4">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($subscriptions as $s)
                <tr class="border-b" style="border-color:var(--border-color);">
                    <td class="py-2 pr-4">{{ $s->user->name ?? '—' }}</td>
                    <td class="py-2 pr-4">{!! \App\Support\Money::coin($s->amount) !!}</td>
                    <td class="py-2 pr-4 text-muted">{{ $s->payment_method }}</td>
                    <td class="py-2 pr-4">{{ $s->status }}</td>
                    <td class="py-2 pr-4 text-muted">{{ $s->expires_at?->format('M j, Y') ?? '—' }}</td>
                    <td class="py-2 pr-4">
                        @if($s->status === 'active')
                        <form method="POST" action="{{ route('admin.subscriptions.cancel', $s) }}" onsubmit="return confirm('Cancel this subscription?');">
                            @csrf
                            <button class="balance-chip" style="font-size:0.75rem;color:#ff6b6b;">Cancel</button>
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $subscriptions->links() }}</div>
</div>
@endsection
