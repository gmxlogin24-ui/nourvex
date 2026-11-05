@extends('layouts.app-admin')
@section('content')
@php $M = \App\Support\Money::class; @endphp
<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Withdrawals</h1>
        <a href="{{ route('admin.finance.profit') }}" class="text-accent text-sm">← Profit and pool</a>
    </div>

    <div class="grid gap-3 mb-4" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));">
        @foreach($totals as $t)
        <a href="{{ route('admin.finance.withdrawals', ['status' => $t->status]) }}" class="stat-card">
            <p class="text-muted text-sm">{{ ucfirst($t->status) }} · {{ $t->c }}</p>
            <p class="font-bold">{!! $M::coin($t->a) !!}</p>
            <p class="text-muted text-sm">fees {{ $M::q($t->f) }}</p>
        </a>
        @endforeach
    </div>

    <form method="GET" class="flex gap-2 mb-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="User or reference" class="balance-chip" style="background:var(--color-card);border:1px solid var(--border-color);">
        <input type="text" name="status" value="{{ request('status') }}" placeholder="Status" class="balance-chip" style="background:var(--color-card);border:1px solid var(--border-color);">
        <button class="balance-chip primary">Filter</button>
    </form>

    <div class="stat-card overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-muted border-b" style="border-color:var(--border-color);">
                <th class="py-2 pr-4">Date</th><th class="py-2 pr-4">User</th><th class="py-2 pr-4">Amount</th><th class="py-2 pr-4">Fee</th><th class="py-2 pr-4">Bank</th><th class="py-2 pr-4">Status</th><th class="py-2">Reference</th>
            </tr></thead>
            <tbody>
            @forelse($withdrawals as $w)
            <tr class="border-b" style="border-color:var(--border-color);">
                <td class="py-2 pr-4 text-muted">{{ $w->created_at->format('M j, Y g:ia') }}</td>
                <td class="py-2 pr-4">{{ $w->user->name ?? '—' }}</td>
                <td class="py-2 pr-4">{!! $M::coin($w->amount) !!}</td>
                <td class="py-2 pr-4">{{ $M::q($w->fee) }}</td>
                <td class="py-2 pr-4 text-muted">{{ $w->bank_name }} ****{{ substr((string) $w->account_number, -4) }}</td>
                <td class="py-2 pr-4">{{ $w->status }}</td>
                <td class="py-2 text-muted">{{ $w->tx_ref }}</td>
            </tr>
            @empty
            <tr><td class="py-2 text-muted" colspan="7">No withdrawals found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $withdrawals->links() }}</div>
</div>
@endsection
