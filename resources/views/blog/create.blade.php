@extends('layouts.app')
@section('content')


<main class="max-w-2xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-6">Write a Blog Post</h1>

    @if ($errors->any())
    <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
    @endif

    <div class="glass-card p-6">
        <form method="POST" action="{{ route('blog.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="text" name="title" placeholder="Title" required class="input-field !mt-0">
            <input type="file" name="cover_image" accept="image/*" class="input-field !mt-0">
            <textarea name="body" placeholder="Write your post..." required rows="10" class="input-field !mt-0"></textarea>
            <button type="submit" class="btn-primary">Publish</button>
        </form>
    </div>
</main>
@endsection
