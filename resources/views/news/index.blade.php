@extends('layouts.app')
@section('content')


<main class="max-w-4xl mx-auto p-6">
    <div class="flex justify-between items-center mb-2">
        <div>
            <h1 class="text-2xl font-semibold">Post to Buzz</h1>
            <p class="text-muted text-sm">Admin only — published posts appear live in Pulse</p>
        </div>
        <a href="{{ route('pulse.index') }}" class="btn-secondary text-sm"><i data-lucide="zap" class="w-4 h-4"></i> View in Pulse</a>
    </div>

    <div class="glass-card p-6 my-6">
        <form method="POST" action="{{ route('news.store') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <input type="text" name="title" placeholder="Title" required class="input-field !mt-0">
            <input type="text" name="category" placeholder="Category (e.g. SUG, Campus, Platform)" required class="input-field !mt-0">
            <select name="type" class="input-field !mt-0">
                <option value="news">News</option>
                <option value="ad">Sponsored / Ad</option>
            </select>
            <textarea name="body" placeholder="Content" required rows="3" class="input-field !mt-0"></textarea>
            <div class="grid grid-cols-2 gap-3">
                <input type="file" name="image" accept="image/*" class="input-field !mt-0">
                <input type="file" name="video" accept="video/mp4,video/quicktime" class="input-field !mt-0">
            </div>
            <input type="url" name="source_url" placeholder="Source link (optional)" class="input-field !mt-0">
            <button type="submit" class="btn-primary text-sm">Publish</button>
        </form>
    </div>

    <div class="glass-card p-6">
        <h2 class="font-semibold mb-4">Recently published ({{ count($newsItems) }})</h2>
        @forelse($newsItems->take(5) as $item)
        <div class="tx-table-row">
            <div>
                <p class="text-sm font-medium">{{ $item->title }}</p>
                <p class="text-muted text-xs">{{ $item->type === 'ad' ? 'Sponsored' : $item->category }} · {{ $item->published_at->diffForHumans() }}</p>
            </div>
        </div>
        @empty
        <p class="text-muted text-sm">Nothing published yet.</p>
        @endforelse
    </div>
</main>
@endsection
