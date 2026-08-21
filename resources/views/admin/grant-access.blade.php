@extends('layouts.app-admin')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Grant Access</h1>
        <a href="{{ route('admin.index') }}" class="text-accent text-sm">← Back</a>
    </div>

    <p class="text-muted text-sm mb-4">Manually activate an account and set its tier, bypassing payment — for users who paid another way (cash, etc.).</p>

    @if(session('status'))
    <div class="stat-card mb-4" style="border-color:var(--color-accent);"><p>{{ session('status') }}</p></div>
    @endif

    <form method="GET" class="flex gap-2 mb-4">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search name or email" class="balance-chip" style="background:var(--color-card);border:1px solid var(--border-color);flex:1;">
        <button class="balance-chip primary">Search</button>
    </form>

    @if($users->isNotEmpty())
    <div class="stat-card overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-muted border-b" style="border-color:var(--border-color);">
                    <th class="py-2 pr-4">Name</th>
                    <th class="py-2 pr-4">Email</th>
                    <th class="py-2 pr-4">Current Tier</th>
                    <th class="py-2 pr-4">Paid?</th>
                    <th class="py-2 pr-4">Grant</th>
                </tr>
            </thead>
            <tbody>
            @foreach($users as $u)
                <tr class="border-b" style="border-color:var(--border-color);">
                    <td class="py-2 pr-4">{{ $u->name }}</td>
                    <td class="py-2 pr-4 text-muted">{{ $u->email }}</td>
                    <td class="py-2 pr-4">{{ $u->tier }}</td>
                    <td class="py-2 pr-4">{{ $u->signup_fee_paid ? 'Yes' : 'No' }}</td>
                    <td class="py-2 pr-4">
                        <form method="POST" action="{{ route('admin.grant-access.store') }}" class="flex gap-1">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $u->id }}">
                            <select name="tier" style="background:var(--color-card);border:1px solid var(--border-color);border-radius:0.5rem;font-size:0.75rem;padding:0 0.4rem;">
                                <option value="tier1">Tier 1</option>
                                <option value="tier2">Tier 2</option>
                                <option value="tier3">Tier 3</option>
                            </select>
                            <button class="balance-chip primary" style="font-size:0.75rem;">Grant</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @elseif($search)
    <p class="text-muted text-sm">No users found for "{{ $search }}".</p>
    @endif
</div>
@endsection
