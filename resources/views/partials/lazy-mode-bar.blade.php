@php $wrapId = 'lazy-wrap-' . $assignType; @endphp
<div style="position:fixed;bottom:0;left:72px;right:0;z-index:60;display:flex;justify-content:flex-end;padding:1rem;pointer-events:none;">
    <div id="{{ $wrapId }}-panel" class="stat-card" data-assign-url="{{ route('admin.disputes.assign') }}"
        style="pointer-events:auto;display:none;flex-direction:column;gap:0.6rem;padding:0.8rem 1rem;min-width:280px;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <strong style="font-size:0.85rem;">Lazy Mode</strong>
            <span id="{{ $wrapId }}-progress" style="font-size:0.75rem;color:var(--color-muted);"></span>
        </div>
        <select id="{{ $wrapId }}-agent" class="balance-chip" style="width:100%;background:var(--color-card);border:1px solid var(--border-color);">
            <option value="">Select customer care agent…</option>
            @foreach($ccAgents as $agent)
                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
            @endforeach
        </select>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
            <button type="button" class="balance-chip primary" style="font-size:0.75rem;" onclick="lazyPushAll('{{ $assignType }}','{{ $wrapId }}')">Push All to Agent</button>
            <button type="button" class="balance-chip" style="font-size:0.75rem;" onclick="lazyStartOneByOne('{{ $assignType }}','{{ $wrapId }}')">One by One</button>
        </div>
        <div id="{{ $wrapId }}-step-controls" style="display:none;gap:0.5rem;flex-wrap:wrap;">
            <button type="button" class="balance-chip primary" style="font-size:0.75rem;" onclick="lazyAssignCurrent('{{ $assignType }}','{{ $wrapId }}')">Assign &amp; Next</button>
            <button type="button" class="balance-chip" style="font-size:0.75rem;" onclick="lazySkipCurrent('{{ $wrapId }}')">Skip</button>
        </div>
        <button type="button" class="balance-chip" style="font-size:0.7rem;align-self:flex-end;" onclick="lazyClosePanel('{{ $wrapId }}')">Close</button>
    </div>
    <button type="button" style="pointer-events:auto;font-size:0.8rem;margin-left:0.5rem;box-shadow:var(--shadow-medium);" class="balance-chip primary" onclick="lazyTogglePanel('{{ $wrapId }}')">😴 Lazy Mode</button>
</div>
