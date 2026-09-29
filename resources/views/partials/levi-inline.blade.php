@auth
<div class="levi-chat-widget levi-inline" data-endpoint="{{ route('concierge.message') }}">
    <div id="leviTourTrigger" data-seen="{{ auth()->user()->has_seen_tour ? '1' : '0' }}" hidden></div>
    <div class="levi-inline-header">
        <span class="levi-inline-badge">✦ LEVI</span>
        <span class="levi-inline-sub">Ask me anything about NOURVEX</span>
    </div>
    <div class="levi-messages levi-inline-messages">
        <div class="levi-msg levi-msg-ai">Hey{{ auth()->user()->name ? ', ' . explode(' ', auth()->user()->name)[0] : '' }} — what do you need? I can find gigs, check your balance, or take you somewhere.</div>
    </div>
    <div class="levi-chips">
        <button type="button" class="levi-chip" data-fill="Check my wallet balance">Wallet balance</button>
        <button type="button" class="levi-chip" data-fill="Find gigs under 20,000 naira">Gigs under ₦20k</button>
        <button type="button" class="levi-chip" data-fill="Claim today's Q Coin reward">Claim Q Coins</button>
    </div>
    <form class="levi-form levi-inline-input">
        <input class="levi-input" type="text" placeholder="Type here..." autocomplete="off">
        <button type="button" class="levi-mic-btn" aria-label="Speak">🎤</button>
            <button type="submit" aria-label="Send">→</button>
    </form>
</div>
@endauth
