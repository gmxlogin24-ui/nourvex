@extends('layouts.app-admin')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Transactions</h1>
        <a href="{{ route('admin.index') }}" class="text-accent text-sm">← Back</a>
    </div>

    <form method="GET" class="flex gap-2 mb-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search user" class="balance-chip" style="background:var(--color-card);border:1px solid var(--border-color);">
        <button class="balance-chip primary">Filter</button>
    </form>

    <div class="stat-card overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-muted border-b" style="border-color:var(--border-color);">
                    <th class="py-2 pr-4">User</th>
                    <th class="py-2 pr-4">Type</th>
                    <th class="py-2 pr-4">Amount</th>
                    <th class="py-2 pr-4">Date</th>
                </tr>
            </thead>
            <tbody>
            @foreach($transactions as $t)
                <tr class="border-b" style="border-color:var(--border-color);">
                    <td class="py-2 pr-4">{{ $t->user->name ?? '—' }}</td>
                    <td class="py-2 pr-4 text-muted">{{ $t->type }}</td>
                    <td class="py-2 pr-4" style="color:{{ $t->amount >= 0 ? '#4ade80' : '#ff6b6b' }};">₦{{ number_format($t->amount, 2) }}</td>
                    <td class="py-2 pr-4 text-muted">{{ $t->created_at->format('M j, Y g:ia') }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $transactions->links() }}</div>
</div>
@endsection
