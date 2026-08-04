<div class="balance-folder reveal-on-scroll" id="balanceFolder">
    <span class="folder-tab"></span>
    <div class="folder-stack-edge"></div>

    <div class="folder-card-slot" id="folderCardSlot">
        <div class="glass-balance-card" id="glassBalanceCard">
            <div class="glass-balance-eyebrow">
                <span><span class="live-dot"></span>Total Balance</span>
                <span>{{ now()->format('M j') }}</span>
            </div>
            <p class="glass-balance-amount" id="glassBalanceAmount" data-target="{{ (float) ($balance ?? 0) }}">₦0.00</p>
            <p class="glass-balance-sub">Available across wallet &amp; virtual account</p>
            <div class="glass-balance-actions">
                <a href="{{ route('wallet.index') }}" class="glass-chip primary"><i data-lucide="plus" class="w-4 h-4"></i> Top Up</a>
                <a href="{{ route('wallet.index') }}" class="glass-chip"><i data-lucide="arrow-down-to-line" class="w-4 h-4"></i> Withdraw</a>
            </div>
        </div>
    </div>

    <div class="folder-front"></div>
</div>

<script>
(function(){
    var folder = document.getElementById('balanceFolder');
    var slot = document.getElementById('folderCardSlot');
    if (!folder || !slot) return;

    var tapTimer = null, tapCount = 0;

    function openFull(){ folder.classList.remove('is-peek'); folder.classList.add('is-open'); }
    function peek(){ folder.classList.toggle('is-peek'); }
    function closeAll(){ folder.classList.remove('is-open','is-peek'); }

    function handleTap(e){
        var link = e.target.closest('a,button');
        if (link) {
            if (!folder.classList.contains('is-open')) { e.preventDefault(); }
            else { return; }
        }
        tapCount++;
        if (tapCount === 1) {
            tapTimer = setTimeout(function(){
                tapCount = 0;
                folder.classList.contains('is-open') ? closeAll() : peek();
            }, 280);
        } else if (tapCount === 2) {
            clearTimeout(tapTimer);
            tapCount = 0;
            folder.classList.contains('is-open') ? closeAll() : openFull();
        }
    }

    slot.addEventListener('click', handleTap);
    document.addEventListener('click', function(e){
        if (folder.classList.contains('is-open') && !folder.contains(e.target)) closeAll();
    });
})();
</script>
