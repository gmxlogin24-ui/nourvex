<a href="{{ $it['url'] ?? '#' }}" class="pfn-row">
  <span class="pfn-th">@if(!empty($it['img']))<img src="{{ $it['img'] }}" alt="" loading="lazy">@else<i data-lucide="{{ $it['icon'] ?? 'circle' }}"></i>@endif</span>
  <span class="pfn-rb"><b>{{ \Illuminate\Support\Str::limit($it['title'] ?? 'Untitled', 46) }}</b><small>{{ $it['meta'] ?? '' }}</small></span>
  @if(!empty($it['amt']))<span class="pfn-am">{{ $it['amt'] }}</span>@endif
</a>
