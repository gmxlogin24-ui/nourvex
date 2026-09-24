@php
    $compact = $compact ?? false;
    $tagLabel = $tagLabel ?? 'Sponsored';
    $placeholderSeeds = ['gigA', 'gigB', 'gigC', 'gigD', 'gigE', 'gigF'];
@endphp
<div class="sponsor-stack {{ $compact ? 'compact' : '' }}">
    @forelse($items as $i => $item)
    <div class="sponsor-card">
        @if(!empty($item['video_url']))
        <video src="{{ $item['video_url'] }}" autoplay muted loop playsinline></video>
        @elseif(!empty($item['image_url']))
        <img src="{{ $item['image_url'] }}" alt="{{ $item['title'] ?? 'Sponsored' }}">
        @else
        {{-- Default placeholder photo — swap automatically once real sponsors are added --}}
        <img src="https://picsum.photos/seed/{{ $placeholderSeeds[$i % count($placeholderSeeds)] }}/600/800" alt="Sponsored spot" loading="lazy">
        @endif
        <div class="sponsor-card-label">
            <span class="tag">{{ $tagLabel }}</span>
            <span class="name">{{ $item['title'] ?? 'Featured' }}</span>
        </div>
    </div>
    @empty
    <div class="sponsor-card">
        <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:var(--color-surface);">
            <span class="text-muted text-sm">No sponsored spots yet</span>
        </div>
    </div>
    @endforelse
</div>
