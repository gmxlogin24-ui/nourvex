@extends('layouts.app-admin')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Conversations</h1>
        <a href="{{ route('admin.index') }}" class="text-accent text-sm">← Back</a>
    </div>
    <div class="stat-card overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-muted border-b" style="border-color:var(--border-color);">
                    <th class="py-2 pr-4">Type</th>
                    <th class="py-2 pr-4">Subject</th>
                    <th class="py-2 pr-4">Participants</th>
                    <th class="py-2 pr-4">Last message</th>
                    <th class="py-2 pr-4"></th>
                </tr>
            </thead>
            <tbody>
            @foreach($conversations as $c)
                <tr class="border-b" style="border-color:var(--border-color);">
                    <td class="py-2 pr-4">{{ $c->type }}</td>
                    <td class="py-2 pr-4 text-muted">{{ class_basename($c->subject_type) }} #{{ $c->subject_id }}</td>
                    <td class="py-2 pr-4">{{ $c->participants->pluck('user.name')->filter()->join(', ') }}</td>
                    <td class="py-2 pr-4 text-muted">{{ $c->last_message_at?->diffForHumans() ?? '—' }}</td>
                    <td class="py-2 pr-4"><a href="{{ route('admin.conversations.show', $c) }}" class="text-accent">View</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $conversations->links() }}</div>
</div>
@endsection
