@extends('layouts.app-admin')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Orders — Tracking Verification</h1>
        <a href="{{ route('customer-care.index') }}" class="text-accent text-sm">← Back</a>
    </div>

    @if(session('status'))<div class="stat-card mb-4" style="border-color:var(--color-accent);"><p>{{ session('status') }}</p></div>@endif
    @if ($errors->any())<div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>@endif

    <form method="GET" class="flex gap-2 mb-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tracking ID" class="balance-chip" style="background:var(--color-card);border:1px solid var(--border-color);">
        <button class="balance-chip primary">Search</button>
    </form>

    <div class="stat-card overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-muted border-b" style="border-color:var(--border-color);">
                <th class="py-2 pr-4">Order #</th><th class="py-2 pr-4">Listing</th><th class="py-2 pr-4">Buyer</th>
                <th class="py-2 pr-4">Tracking ID</th><th class="py-2 pr-4">Status</th><th class="py-2 pr-4">Action</th>
            </tr></thead>
            <tbody>
            @foreach($orders as $o)
                <tr class="border-b" style="border-color:var(--border-color);">
                    <td class="py-2 pr-4">#{{ $o->id }}</td>
                    <td class="py-2 pr-4">{{ $o->listing->title ?? '—' }}</td>
                    <td class="py-2 pr-4 text-muted">{{ $o->buyer->name ?? '—' }}</td>
                    <td class="py-2 pr-4">{{ $o->tracking_id ?? '—' }}</td>
                    <td class="py-2 pr-4">{{ $o->status }}{{ $o->delivery_confirmed_at ? ' · confirmed' : '' }}</td>
                    <td class="py-2 pr-4">
                        @if(!$o->delivery_confirmed_at && $o->tracking_id)
                        <form method="POST" action="{{ route('customer-care.orders.confirm-delivery', $o) }}" class="flex gap-1">
                            @csrf
                            <input type="text" name="tracking_id" placeholder="Enter tracking ID" style="width:120px;background:var(--color-card);border:1px solid var(--border-color);border-radius:0.5rem;padding:0 0.4rem;font-size:0.75rem;">
                            <button class="balance-chip primary" style="font-size:0.75rem;">Confirm</button>
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
</div>
@endsection
