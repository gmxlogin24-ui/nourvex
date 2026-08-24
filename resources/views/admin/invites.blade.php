@extends('layouts.app-admin')
@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Customer Care Invites</h1>
        <a href="{{ route('admin.index') }}" class="text-accent text-sm">← Back</a>
    </div>

    @if(session('status'))
    <div class="stat-card mb-4" style="border-color:var(--color-accent);"><p>{{ session('status') }}</p></div>
    @endif

    <form method="POST" action="{{ route('admin.invites.store') }}" class="flex gap-2 mb-6 items-center flex-wrap">
        @csrf
        <label class="text-sm text-muted">Expires in
            <select name="expires_in_days" class="balance-chip" style="background:var(--color-card);border:1px solid var(--border-color);">
                <option value="1">1 day</option>
                <option value="3">3 days</option>
                <option value="7" selected>7 days</option>
                <option value="14">14 days</option>
                <option value="30">30 days</option>
            </select>
        </label>
        <button class="balance-chip primary">Generate Invite Link</button>
    </form>

    <div class="stat-card overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-muted border-b" style="border-color:var(--border-color);">
                    <th class="py-2 pr-4">Link</th>
                    <th class="py-2 pr-4">Created By</th>
                    <th class="py-2 pr-4">Expires</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($invites as $inv)
                <tr class="border-b" style="border-color:var(--border-color);">
                    <td class="py-2 pr-4">
                        <input type="text" readonly value="{{ url('/register/invite/' . $inv->code) }}" onclick="this.select()" style="width:220px;background:var(--color-card);border:1px solid var(--border-color);border-radius:0.5rem;padding:0.2rem 0.4rem;font-size:0.7rem;">
                    </td>
                    <td class="py-2 pr-4 text-muted">{{ $inv->creator->name ?? '—' }}</td>
                    <td class="py-2 pr-4 text-muted">{{ $inv->expires_at->format('M j, Y') }}</td>
                    <td class="py-2 pr-4">
                        @if($inv->used_at)
                            Used by {{ $inv->usedBy->name ?? '—' }}
                        @elseif($inv->expires_at->isPast())
                            Expired
                        @else
                            Active
                        @endif
                    </td>
                    <td class="py-2 pr-4">
                        @if(!$inv->used_at)
                        <form method="POST" action="{{ route('admin.invites.revoke', $inv) }}" onsubmit="return confirm('Revoke this invite link?');">
                            @csrf
                            @method('DELETE')
                            <button class="balance-chip" style="font-size:0.75rem;">Revoke</button>
                        </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $invites->links() }}</div>
</div>
@endsection
