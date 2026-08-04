@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Gigs</h1>
        <a href="{{ route('admin.index') }}" class="text-accent text-sm">← Back</a>
    </div>

    @if(session('status'))
    <div class="stat-card mb-4" style="border-color:var(--color-accent);"><p>{{ session('status') }}</p></div>
    @endif

    <form method="GET" class="flex gap-2 mb-4">
        <select name="status" class="balance-chip" style="background:var(--color-card);border:1px solid var(--border-color);">
            <option value="">All statuses</option>
            <option value="open" @selected(request('status')==='open')>Open</option>
            <option value="completed" @selected(request('status')==='completed')>Completed</option>
        </select>
        <button class="balance-chip primary">Filter</button>
    </form>

    <div class="stat-card overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-muted border-b" style="border-color:var(--border-color);">
                    <th class="py-2 pr-4">Title</th>
                    <th class="py-2 pr-4">Poster</th>
                    <th class="py-2 pr-4">Amount</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2 pr-4">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($gigs as $g)
                <tr class="border-b" style="border-color:var(--border-color);">
                    <td class="py-2 pr-4">{{ $g->title }}</td>
                    <td class="py-2 pr-4 text-muted">{{ $g->user->name ?? '—' }}</td>
                    <td class="py-2 pr-4">₦{{ number_format($g->amount) }}</td>
                    <td class="py-2 pr-4">{{ $g->status }}</td>
                    <td class="py-2 pr-4">
                        <form method="POST" action="{{ route('admin.gigs.delete', $g) }}" onsubmit="return confirm('Remove this gig?');">
                            @csrf @method('DELETE')
                            <button class="balance-chip" style="font-size:0.75rem;color:#ff6b6b;">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $gigs->links() }}</div>
</div>
@endsection
