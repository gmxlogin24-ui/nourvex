@extends('layouts.app-authenticated')
@section('content')


<main class="max-w-2xl mx-auto p-6">
    <a href="{{ route('blog.index') }}" class="text-muted text-sm flex items-center gap-1 mb-4"><i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Blog</a>

    <div class="glass-card p-8">
        @if($blog->cover_image)
        <img src="{{ $blog->cover_image }}" class="w-full h-56 object-cover rounded-xl mb-5">
        @endif
        <h1 class="text-2xl font-semibold">{{ $blog->title }}</h1>
        <p class="text-muted text-sm mt-2">By {{ $blog->author->name ?? 'Unknown' }} · {{ $blog->created_at->diffForHumans() }}</p>
        <div class="mt-6 text-sm" style="white-space:pre-line;">{{ $blog->body }}</div>
    </div>
</main>
@endsection
