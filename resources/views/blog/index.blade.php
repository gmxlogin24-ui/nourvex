@extends('layouts.app-authenticated')
@section('content')


<main class="max-w-4xl mx-auto p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-semibold">Blog</h1>
            <p class="text-muted text-sm">Stories and guides from the Qelvo community</p>
        </div>
        @auth
        <a href="{{ route('blog.create') }}" class="btn-primary text-sm"><i data-lucide="plus" class="w-4 h-4"></i> Write</a>
        @endauth
    </div>

    <div class="grid md:grid-cols-2 gap-5">
        @forelse($blogs as $post)
        <a href="{{ route('blog.show', $post->id) }}" class="glass-card p-5" style="text-decoration:none;color:inherit;">
            @if($post->cover_image)
            <img src="{{ $post->cover_image }}" class="w-full h-40 object-cover rounded-xl mb-3">
            @endif
            <h3 class="font-semibold">{{ $post->title }}</h3>
            <p class="text-muted text-sm mt-1 line-clamp-2">{{ Str::limit(strip_tags($post->body), 100) }}</p>
            <p class="text-muted text-xs mt-3">By {{ $post->author->name ?? 'Unknown' }} · {{ $post->created_at->diffForHumans() }}</p>
        </a>
        @empty
        <p class="text-muted text-sm">No blog posts yet.</p>
        @endforelse
    </div>
</main>
@endsection
