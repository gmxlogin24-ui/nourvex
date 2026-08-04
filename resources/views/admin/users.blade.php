@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Users</h1>
        <a href="{{ route('admin.index') }}" class="text-accent text-sm">← Back</a>
    </div>

    @if(session('status'))
    <div class="stat-card mb-4" style="border-color:var(--color-accent);"><p>{{ session('status') }}</p></div>
    @endif

    <form method="GET" class="flex gap-2 mb-4 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email" class="balance-chip" style="background:var(--color-card);border:1px solid var(--border-color);">
        <select name="tier" class="balance-chip" style="background:var(--color-card);border:1px solid var(--border-color);">
            <option value="">All tiers</option>
            <option value="free" @selected(request('tier')==='free')>Free</option>
            <option value="tier1" @selected(request('tier')==='tier1')>Tier 1</option>
        </select>
        <button class="balance-chip primary">Filter</button>
    </form>

    <div class="stat-card overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-muted border-b" style="border-color:var(--border-color);">
                    <th class="py-2 pr-4">Name</th>
                    <th class="py-2 pr-4">Email</th>
                    <th class="py-2 pr-4">Tier</th>
                    <th class="py-2 pr-4">Balance</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($users as $u)
                <tr class="border-b" style="border-color:var(--border-color);">
                    <td class="py-2 pr-4">{{ $u->name }}</td>
                    <td class="py-2 pr-4 text-muted">{{ $u->email }}</td>
                    <td class="py-2 pr-4">{{ $u->tier }}</td>
                    <td class="py-2 pr-4">₦{{ number_format($u->wallet_balance, 2) }}</td>
                    <td class="py-2 pr-4">
                        {{ $u->is_admin ? 'Admin' : 'User' }}{{ $u->is_banned ? ' · Banned' : '' }}
                    </td>
                    <td class="py-2 pr-4 flex gap-2 flex-wrap">
                        <form method="POST" action="{{ route('admin.toggle-admin', $u) }}">
                            @csrf
                            <button class="balance-chip" style="font-size:0.75rem;">{{ $u->is_admin ? 'Remove Admin' : 'Make Admin' }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.toggle-ban', $u) }}">
                            @csrf
                            <button class="balance-chip" style="font-size:0.75rem;">{{ $u->is_banned ? 'Unban' : 'Ban' }}</button>
                        </form>
                        <form method="POST" action="{{ route('admin.adjust-balance', $u) }}" class="flex gap-1">
                            @csrf
                            <input type="number" step="0.01" name="amount" placeholder="+/- amt" style="width:90px;background:var(--color-card);border:1px solid var(--border-color);border-radius:0.5rem;padding:0 0.4rem;font-size:0.75rem;">
                            <button class="balance-chip primary" style="font-size:0.75rem;">Apply</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection
