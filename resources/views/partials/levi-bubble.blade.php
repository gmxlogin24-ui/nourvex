@auth
<div id="leviRoot" class="levi-root">
    <button id="leviBubble" class="levi-bubble" aria-label="Ask LEVI" onclick="alert('DIRECT HTML CLICK WORKED')">
        <span class="levi-bubble-glow"></span>
        <span class="levi-bubble-icon">✦</span>
    </button>

    <div id="leviPanel" class="levi-panel" hidden>
        <div id="leviBackdrop" class="levi-panel-backdrop"></div>
        <div class="levi-panel-card levi-chat-widget" data-endpoint="{{ route('concierge.message') }}">
            <div class="levi-panel-header">
                <span class="levi-panel-title">LEVI</span>
                <button class="levi-close" aria-label="Close">×</button>
            </div>
            <div class="levi-messages levi-panel-messages">
                <div class="levi-msg levi-msg-ai">Hey{{ auth()->user()->name ? ', ' . explode(' ', auth()->user()->name)[0] : '' }} — what do you need?</div>
            </div>
            <div class="levi-chips">
                <button type="button" class="levi-chip" data-fill="Check my wallet balance">Wallet balance</button>
                <button type="button" class="levi-chip" data-fill="Find gigs under 20,000 naira">Gigs under ₦20k</button>
                <button type="button" class="levi-chip" data-fill="Claim today's Q Coin reward">Claim Q Coins</button>
            </div>
            <form class="levi-form levi-panel-input">
                <input class="levi-input" type="text" placeholder="Ask LEVI anything..." autocomplete="off">
                <button type="button" class="levi-mic-btn" aria-label="Speak">🎤</button>
            <button type="submit" aria-label="Send">→</button>
            </form>
        </div>
    </div>
</div>
@endauth
