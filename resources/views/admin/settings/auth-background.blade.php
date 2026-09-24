@extends('layouts.app-admin')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Login/Register Background</h1>
        <a href="{{ route('admin.index') }}" class="text-accent text-sm">← Back</a>
    </div>

    @if(session('status'))
    <div class="stat-card mb-4" style="border-color:var(--color-accent);"><p>{{ session('status') }}</p></div>
    @endif
    @if($errors->any())
    <div class="stat-card mb-4" style="border-color:#ef4444;"><p>{{ $errors->first() }}</p></div>
    @endif

    <div class="stat-card mb-4">
        <p class="text-sm text-muted mb-2">Current background:</p>
        @if($current)
            <img src="{{ $current }}" style="width:100%;max-width:400px;border-radius:12px;">
        @else
            <p class="text-sm text-muted">No custom image set — using the default abstract background.</p>
        @endif
    </div>

    <div class="stat-card">
        <form method="POST" action="{{ route('admin.settings.auth-background.update') }}" enctype="multipart/form-data" class="flex flex-col gap-3">
            @csrf
            <input type="file" name="image" accept="image/*" required>
            <button class="balance-chip primary" style="align-self:flex-start;">Upload New Background</button>
        </form>
        @if($current)
        <form method="POST" action="{{ route('admin.settings.auth-background.reset') }}" class="mt-3">
            @csrf
            <button class="balance-chip" style="font-size:0.8rem;">Revert to Default</button>
        </form>
        @endif
    </div>
</div>
@endsection
