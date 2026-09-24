@extends('layouts.app-admin')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold">{{ class_basename($conversation->subject_type) }} #{{ $conversation->subject_id }} thread</h1>
        <a href="{{ route('admin.conversations.index') }}" class="text-accent text-sm">← Back</a>
    </div>
    @if(session('status'))<div class="stat-card mb-4" style="border-color:var(--color-accent);"><p>{{ session('status') }}</p></div>@endif
    <div class="stat-card mb-4" style="max-height:500px;overflow-y:auto;">
        @forelse($messages as $m)
        <div class="text-sm py-2 border-b flex justify-between items-start gap-2" style="border-color:var(--border-color);">
            <div>
                <span class="font-medium">{{ $m->sender->name ?? ($m->is_system ? 'System' : '—') }}:</span>
                {{ $m->body }}
                <span class="text-muted text-xs"> · {{ $m->created_at->diffForHumans() }}</span>
            </div>
            <form method="POST" action="{{ route('admin.conversations.messages.delete', [$conversation, $m]) }}" onsubmit="return confirm('Delete this message?');">
                @csrf @method('DELETE')
                <button class="text-xs" style="color:#ff6b6b;">Delete</button>
            </form>
        </div>
        @empty
        <p class="text-muted text-sm">No messages.</p>
        @endforelse
    </div>
</div>
@endsection
