@php
    $item = $item ?? [];
    $imageUrl = $item['image_url'] ?? null;
    $videoUrl = $item['video_url'] ?? null;
    $title = $item['title'] ?? 'Featured on Qelvo';
    $subtitle = $item['subtitle'] ?? null;
    $ctaText = $item['cta_text'] ?? 'Shop Now';
    $ctaUrl = $item['cta_url'] ?? route('register');
    $sizeClass = $size ?? '';
    // Placeholder image until real vendor ad content is wired in via the controller.
    $placeholderSeed = $item['placeholder_seed'] ?? 'qelvoad';
@endphp
<a href="{{ $ctaUrl }}" class="exhibition-card {{ $sizeClass }}">
    @if($videoUrl)
        <video src="{{ $videoUrl }}" autoplay muted loop playsinline></video>
    @elseif($imageUrl)
        <img src="{{ $imageUrl }}" alt="{{ $title }}">
    @else
        <img src="https://picsum.photos/seed/{{ $placeholderSeed }}/700/500" alt="{{ $title }}" loading="lazy">
    @endif
    <div class="exhibition-overlay">
        <div>
            <p class="ex-title">{{ $title }}</p>
            @if($subtitle)
            <p class="ex-sub">{{ $subtitle }}</p>
            @endif
        </div>
        <span class="ex-cta">{{ $ctaText }}</span>
    </div>
</a>
