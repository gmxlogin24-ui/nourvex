@extends('layouts.app-admin')
@section('content')
@php $M = \App\Support\Money::class; @endphp
<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Profit and Q Coin pool</h1>
        <span><a href="{{ route('admin.finance.pricing') }}" class="text-accent text-sm mr-4">Prices →</a><a href="{{ route('admin.finance.withdrawals') }}" class="text-accent text-sm">Withdrawals →</a></span>
    </div>
    @if(session('status'))<div class="stat-card mb-4">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="stat-card mb-4" style="color:#ff6b6b;">{{ $errors->first() }}</div>@endif

    <form method="GET" class="flex gap-2 mb-4">
        <input type="month" name="month" value="{{ $month }}" class="balance-chip" style="background:var(--color-card);border:1px solid var(--border-color);">
        <button class="balance-chip primary">Show</button>
    </form>

    <div class="grid gap-3 mb-6" style="grid-template-columns:repeat(auto-fit,minmax(200px,1fr));">
        <div class="stat-card"><p class="text-muted text-sm">Revenue</p><p class="text-xl font-bold">{!! $M::coin($s['revenue_total']) !!}</p></div>
        <div class="stat-card"><p class="text-muted text-sm">Costs</p><p class="text-xl font-bold">{!! $M::coin($s['costs_total']) !!}</p></div>
        <div class="stat-card"><p class="text-muted text-sm">Profit</p><p class="text-xl font-bold">{!! $M::coin($s['profit']) !!}</p></div>
        <div class="stat-card"><p class="text-muted text-sm">Pool ({{ rtrim(rtrim(number_format($s['percent'], 2), '0'), '.') }}% of profit)</p><p class="text-xl font-bold">{!! $M::coin($s['pool']) !!}</p><p class="text-muted text-sm">{{ number_format($s['pool_q']) }} Q to share</p></div>
    </div>

    <div class="grid gap-4 mb-6" style="grid-template-columns:repeat(auto-fit,minmax(300px,1fr));">
        <div class="stat-card">
            <h2 class="font-semibold mb-2">Revenue counted</h2>
            <table class="w-full text-sm">
                @foreach($s['revenue'] as $label => $amt)
                <tr class="border-b" style="border-color:var(--border-color);"><td class="py-2 pr-2">{{ $label }}</td><td class="py-2 text-right">{!! $M::coin($amt) !!}</td></tr>
                @endforeach
            </table>
        </div>
        <div class="stat-card">
            <h2 class="font-semibold mb-2">Costs counted</h2>
            <table class="w-full text-sm">
                @foreach($s['costs'] as $label => $amt)
                <tr class="border-b" style="border-color:var(--border-color);"><td class="py-2 pr-2">{{ $label }}</td><td class="py-2 text-right">{!! $M::coin($amt) !!}</td></tr>
                @endforeach
            </table>
        </div>
    </div>

    <div class="stat-card mb-6">
        <h2 class="font-semibold mb-2">Customer money (not profit)</h2>
        <p class="text-sm text-muted mb-2">Wallets hold money the platform owes users. Your Paystack balance must stay at or above this.</p>
        <p>Total in wallets: {!! $M::coin($s['customer_money']) !!}</p>
        <p class="mt-2">Withdrawals still pending: {!! $M::coin($s['pending_withdrawals']) !!}</p>
    </div>

    @include('admin.finance._settings')

    @if($hasExpenses)
    <div class="stat-card mb-6">
        <h2 class="font-semibold mb-3">Other costs for {{ $month }}</h2>
        <form method="POST" action="{{ route('admin.finance.expenses.store') }}" class="grid gap-3 mb-4" style="grid-template-columns:repeat(auto-fit,minmax(170px,1fr));">
            @csrf
            <select name="category" class="input-field !mt-0">@foreach($categories as $k => $l)<option value="{{ $k }}">{{ $l }}</option>@endforeach</select>
            <input type="number" step="0.01" name="amount" placeholder="Amount (₦)" required class="input-field !mt-0">
            <input type="date" name="incurred_on" value="{{ $month }}-01" required class="input-field !mt-0">
            <input type="text" name="description" placeholder="Note" maxlength="255" class="input-field !mt-0">
            <button class="balance-chip primary">Add cost</button>
        </form>
        <table class="w-full text-sm">
            @forelse($expenses as $e)
            <tr class="border-b" style="border-color:var(--border-color);">
                <td class="py-2 pr-2">{{ $e->incurred_on }}</td><td class="py-2 pr-2">{{ $categories[$e->category] ?? $e->category }}</td>
                <td class="py-2 pr-2 text-muted">{{ $e->description }}</td><td class="py-2 pr-2 text-right">{!! $M::coin($e->amount) !!}</td>
                <td class="py-2 text-right"><form method="POST" action="{{ route('admin.finance.expenses.delete', $e->id) }}" onsubmit="return confirm('Remove this cost?');">@csrf @method('DELETE')<button class="text-muted">Remove</button></form></td>
            </tr>
            @empty
            <tr><td class="py-2 text-muted">No other costs recorded this month.</td></tr>
            @endforelse
        </table>
    </div>
    @else
    <div class="stat-card mb-6">Run the costs migration to enable manual costs: <code>php artisan migrate --path=database/migrations/2026_10_29_000001_create_expenses_table.php</code></div>
    @endif

    <div class="stat-card overflow-x-auto">
        <h2 class="font-semibold mb-2">Transaction types seen in {{ $month }}</h2>
        <table class="w-full text-sm">
            <thead><tr class="text-left text-muted border-b" style="border-color:var(--border-color);"><th class="py-2 pr-4">Type</th><th class="py-2 pr-4">Count</th><th class="py-2 pr-4">Net amount</th><th class="py-2">How it is treated</th></tr></thead>
            <tbody>
            @forelse($types as $t)
            <tr class="border-b" style="border-color:var(--border-color);">
                <td class="py-2 pr-4">{{ $t->type }}</td><td class="py-2 pr-4">{{ $t->c }}</td><td class="py-2 pr-4">{!! $M::forTx($t->type, $t->s) !!}</td>
                <td class="py-2" style="{{ $t->note ? '' : 'color:#f5b942;' }}">{{ $t->note ?? 'Not mapped yet. It is not counted as revenue or cost until we decide.' }}</td>
            </tr>
            @empty
            <tr><td class="py-2 text-muted" colspan="4">No transactions this month.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
