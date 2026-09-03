@extends('layouts.app-admin')

@section('content')
<div class="container mx-auto px-4 py-6 max-w-3xl">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Create Level</h1>
    <form method="POST" action="{{ route('admin.levels.store') }}" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        @include('admin.levels._form')
    </form>
</div>
@endsection
