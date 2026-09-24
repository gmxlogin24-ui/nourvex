@extends('layouts.app-admin')
@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">App Appearance</h1>
        <a href="{{ route('admin.index') }}" class="text-accent text-sm">← Back</a>
    </div>

    @if(session('status'))
    <div class="stat-card mb-4" style="border-color:var(--color-accent);"><p>{{ session('status') }}</p></div>
    @endif
    @if($errors->any())
    <div class="stat-card mb-4" style="border-color:#ef4444;"><p>{{ $errors->first() }}</p></div>
    @endif

    <div class="stat-card mb-4">
        <h2 class="font-semibold mb-3">Login / Register Background</h2>
        <p class="text-sm text-muted mb-2">Current background:</p>
        @if($currentBg)
            <img src="{{ $currentBg }}" style="width:100%;max-width:400px;border-radius:12px;margin-bottom:0.75rem;">
        @else
            <p class="text-sm text-muted mb-3">No custom image set — using the default abstract background.</p>
        @endif

        <form method="POST" action="{{ route('admin.settings.appearance.background') }}" enctype="multipart/form-data" class="flex flex-col gap-3">
            @csrf
            <input type="file" name="image" accept="image/*" required>
            <button class="balance-chip primary" style="align-self:flex-start;">Upload New Background</button>
        </form>
        @if($currentBg)
        <form method="POST" action="{{ route('admin.settings.appearance.background.reset') }}" class="mt-2">
            @csrf
            <button class="balance-chip" style="font-size:0.8rem;">Revert to Default</button>
        </form>
        @endif
    </div>

    <div class="stat-card">
        <h2 class="font-semibold mb-3">Login / Register Card Animation</h2>
        <p class="text-sm text-muted mb-3">Floating decorative elements drifting behind the login/register form.</p>
        <form method="POST" action="{{ route('admin.settings.appearance.animations') }}">
            @csrf
            <label class="flex items-center gap-2 text-sm mb-3">
                <input type="checkbox" name="enabled" value="1" @checked($animatedEnabled)>
                Enable floating elements
            </label>
            <button class="balance-chip primary">Save</button>
        </form>
    </div>
</div>
@endsection
