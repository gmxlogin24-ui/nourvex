<div class="stat-card mb-6">
    <h2 class="font-semibold mb-3">Settings</h2>
    <form method="POST" action="{{ route('admin.finance.settings') }}">
        @csrf
        <label class="text-sm block mb-2" style="max-width:260px">Pool percent of profit<input type="number" step="0.01" min="0" max="100" name="pool_percent" value="{{ $poolPercent }}" class="input-field !mt-1"></label>
        @foreach(collect($fields)->groupBy(fn ($f) => $f[2], true) as $group => $items)
            <h3 class="font-semibold text-sm mb-2 mt-5">{{ $group }}</h3>
            <div class="grid gap-3" style="grid-template-columns:repeat(auto-fit,minmax(190px,1fr));">
                @foreach($items as $key => $f)
                <label class="text-sm">{{ $f[0] }}<input type="number" step="0.01" min="0" name="f_{{ str_replace('.', '_', $key) }}" value="{{ $values[$key] }}" class="input-field !mt-1"></label>
                @endforeach
            </div>
        @endforeach
        <h3 class="font-semibold text-sm mb-2 mt-5">Which transaction types are virtual account deposits?</h3>
        <input type="text" name="dva_types" value="{{ $dvaTypes }}" placeholder="comma separated, for example: virtual_account_deposit" class="input-field !mt-1" maxlength="500">
        <input type="text" name="dva_description" value="{{ $dvaDescription }}" placeholder="description text" class="input-field !mt-2" maxlength="255">
        <p class="text-muted text-sm mt-1">Your webhook records virtual account credits as type wallet_topup with the description "Wallet funded via Paystack". Both boxes must match for a deposit to count. Leave blank to use those.</p>
        <button class="balance-chip primary mt-4">Save settings</button>
    </form>
    <p class="text-muted text-sm mt-3">Defaults are Paystack's published Nigeria pricing, checked 29 Oct 2026:
        <a class="underline" href="https://support.paystack.com/en/articles/2130306" target="_blank" rel="noopener">transactions</a>,
        <a class="underline" href="https://support.paystack.com/en/articles/2130370" target="_blank" rel="noopener">transfers</a>,
        <a class="underline" href="https://support.paystack.com/en/articles/7573314" target="_blank" rel="noopener">stamp duty</a> (₦50 on payouts of ₦10,000 and above, since 18 Feb 2026),
        <a class="underline" href="https://support.paystack.com/en/articles/2124866" target="_blank" rel="noopener">virtual accounts</a>.
        Those pages do not state VAT for Nigeria. If your Paystack statement differs, change the numbers here or add a "Paystack fee correction" cost.</p>
</div>
