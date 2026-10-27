@extends('layouts.auth')
@section('content')
    <div class="flex items-center gap-3 mb-5">
        <div class="logo-badge"></div>
        <h1 class="text-lg font-semibold">NAURVEX</h1>
    </div>
    <p class="text-sm mb-6" style="opacity:0.75;">Where are you based? This puts you on the right leaderboard.</p>
    @if ($errors->any())
    <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('area.update') }}" class="space-y-4">
        @csrf
        <div>
            <label class="text-xs" style="opacity:0.7;">Your area</label>
            <select name="area" required class="glass-input">
                <option value="" disabled {{ $current ? '' : 'selected' }}>Select your area</option>
                @foreach ($areas as $k => $label)
                    <option value="{{ $k }}" {{ old('area', $current) == $k ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="glass-btn-wrap">
            <button type="submit" class="glass-btn" style="width:100%;">Continue</button>
        </div>
    </form>
@endsection
