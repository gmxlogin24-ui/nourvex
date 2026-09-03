@extends('layouts.app-admin')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-3xl">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Edit Badge: {{ $badge->name }}</h1>
    <form method="POST" action="{{ route('admin.badges.update', $badge) }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        @include('admin.badges._form')
    </form>
</div>
@endsection
