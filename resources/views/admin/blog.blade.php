@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Blog Posts</h1>
        <a href="{{ route('admin.index') }}" class="text-accent text-sm">← Back</a>
    </div>

    @if(session('status'))
    <div class="stat-card mb-4" style="border-color:var(--color-accent);"><p>{{ session('status') }}</p></div>
    @endif

    <div class="stat-card overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-muted border-b" style="border-color:var(--border-color);">
                    <th class="py-2 pr-4">Title</th>
                    <th class="py-2 pr-4">Author</th>
                    <th class="py-2 pr-4">Date</th>
                    <th class="py-2 pr-4">Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($posts as $b)
                <tr class="border-b" style="border-color:var(--border-color);">
                    <td class="py-2 pr-4">{{ $b->title }}</td>
                    <td class="py-2 pr-4 text-muted">{{ $b->user->name ?? '—' }}</td>
                    <td class="py-2 pr-4 text-muted">{{ $b->created_at->format('M j, Y') }}</td>
                    <td class="py-2 pr-4">
                        <form method="POST" action="{{ route('admin.blog.delete', $b) }}" onsubmit="return confirm('Delete this post?');">
                            @csrf @method('DELETE')
                            <button class="balance-chip" style="font-size:0.75rem;color:#ff6b6b;">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $posts->links() }}</div>
</div>
@endsection
