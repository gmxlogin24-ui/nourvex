@extends('layouts.app-authenticated')
@section('content')
<main class="max-w-lg mx-auto p-6">
    <a href="{{ route('news.index') }}" class="text-muted text-sm flex items-center gap-1 mb-4">
        <i data-lucide="arrow-left" class="w-4 h-4"></i> Back
    </a>

    @if ($errors->any())
    <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
    @endif

    <div class="glass-card p-6 reveal-on-scroll">
        <h1 class="text-xl font-semibold mb-1">Post an Ad</h1>
        <p class="text-muted text-sm mb-6">Seen by verified students on your campus in Buzz.</p>

        <form method="POST" action="{{ route('news.ad.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="text-sm text-muted">Title</label>
                <input type="text" name="title" required maxlength="255" value="{{ old('title') }}" class="input-field !mt-1">
            </div>
            <div>
                <label class="text-sm text-muted">Category</label>
                <input type="text" name="category" required maxlength="100" value="{{ old('category') }}" class="input-field !mt-1">
            </div>
            <div>
                <label class="text-sm text-muted">Ad Copy</label>
                <textarea name="body" required rows="4" class="input-field !mt-1">{{ old('body') }}</textarea>
            </div>
            <div>
                <label class="text-sm text-muted">Image (optional)</label>
                <input type="file" name="image" accept="image/*" class="input-field !mt-1">
            </div>
            <div>
                <label class="text-sm text-muted mb-2 block">Duration</label>
                <div class="grid grid-cols-3 gap-2">
                    @foreach($pricing as $days => $price)
                    <label class="glass-card p-3 text-center" style="cursor:pointer;">
                        <input type="radio" name="duration" value="{{ $days }}" {{ old('duration') == $days ? 'checked' : '' }} required style="display:none;" onchange="document.querySelectorAll('.dur-label').forEach(el=>el.style.borderColor='transparent');this.closest('label').style.borderColor='var(--color-accent)'">
                        <div class="dur-label" style="border:2px solid transparent;border-radius:0.5rem;padding:0.25rem;">
                            <p class="font-semibold text-sm">{{ $days }} days</p>
                            <p class="text-accent text-xs">₦{{ number_format($price) }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>
            @if(auth()->user()->tier === 'tier3')
            <p class="text-xs text-accent flex items-center gap-1"><i data-lucide="crown" class="w-3.5 h-3.5"></i> Premium: your ad will be pinned at the top automatically.</p>
            @endif
            <button type="submit" class="btn-primary w-full justify-center">Pay & Go Live</button>
        </form>
    </div>
</main>
@endsection
