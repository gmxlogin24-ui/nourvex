@extends('layouts.app-authenticated')

@section('content')
@php
    $plain = (auth()->user()->theme_style ?? 'glass') === 'plain';
    $labels = ['wallet' => 'Wallet', 'id' => 'ID', 'selfie' => 'Selfie', 'address' => 'Address'];
    $order = array_keys($labels);
    $idx = array_search($step, $order, true);
    $idx = $idx === false ? count($order) : $idx;
    $nextKey = $order[$idx + 1] ?? 'done';
    $idTypes = ['national_id' => 'National ID card', 'passport' => 'International passport', 'drivers_license' => 'Driver licence', 'voters_card' => 'Voter card', 'residence_card' => 'Residence card'];
@endphp
<style>
.kyc-wrap{max-width:28rem;margin:0 auto;padding:1.5rem;}
.kyc-steps{display:flex;list-style:none;margin:0 0 1.25rem;padding:0;}
.kyc-step{flex:1;text-align:center;font-size:.72rem;opacity:.55;position:relative;}
.kyc-step::before{content:"";position:absolute;top:.45rem;left:-50%;width:100%;height:2px;background:color-mix(in srgb,currentColor 18%,transparent);}
.kyc-step:first-child::before{display:none;}
.kyc-step.is-reached::before{background:var(--color-accent);}
.kyc-dot{display:block;width:.9rem;height:.9rem;border-radius:50%;margin:0 auto .35rem;background:var(--color-accent);opacity:.3;position:relative;z-index:1;}
.kyc-step.is-active,.kyc-step.is-done{opacity:1;}
.kyc-step.is-active .kyc-dot,.kyc-step.is-done .kyc-dot{opacity:1;}
.kyc-step.is-active .kyc-dot{box-shadow:0 0 0 4px color-mix(in srgb,var(--color-accent) 25%,transparent);}
.kyc-card{padding:1.5rem;}
.kyc-illo{display:grid;place-items:center;height:7.5rem;margin:0 0 1.25rem;border-radius:1rem;background:color-mix(in srgb,var(--color-accent) 10%,transparent);color:var(--color-accent);}
.kyc-title{font-size:1.4rem;font-weight:700;text-align:center;line-height:1.25;margin:0 0 .5rem;}
.kyc-sub{font-size:.85rem;opacity:.7;text-align:center;margin:0 0 1.25rem;}
.kyc-form>*+*{margin-top:1rem;}
.kyc-drop{display:flex;align-items:center;justify-content:space-between;gap:.75rem;padding:.9rem 1rem;border:1.5px dashed color-mix(in srgb,var(--color-accent) 45%,transparent);border-radius:.9rem;cursor:pointer;font-size:.88rem;margin-top:.4rem;}
.kyc-skip{display:block;text-align:center;margin-top:.9rem;font-size:.85rem;opacity:.7;}
.kyc-foot{display:flex;gap:.4rem;align-items:center;justify-content:center;font-size:.75rem;opacity:.6;margin-top:1rem;text-align:center;}
.kyc-list{list-style:none;margin:0 0 1.25rem;padding:0;}
.kyc-list li{display:flex;justify-content:space-between;padding:.6rem 0;border-bottom:1px solid color-mix(in srgb,currentColor 10%,transparent);font-size:.9rem;}
</style>
<main class="kyc-wrap {{ $plain ? 'dqm-page' : '' }}" style="{{ $plain ? 'padding-top:0 !important;' : '' }}">
    <ol class="kyc-steps">
        @foreach ($labels as $key => $label)
            @php $i = array_search($key, $order, true); @endphp
            <li class="kyc-step {{ $key === $step ? 'is-active' : '' }} {{ in_array($key, $done, true) ? 'is-done' : '' }} {{ $i <= $idx ? 'is-reached' : '' }}"><span class="kyc-dot"></span>{{ $label }}</li>
        @endforeach
    </ol>

    @if ($errors->any())
        <div class="bg-red-500/10 text-red-500 text-sm rounded-lg p-3 mb-4">{{ $errors->first() }}</div>
    @endif
    @if (session('status'))
        <div class="bg-green-500/10 text-green-500 text-sm rounded-lg p-3 mb-4">{{ session('status') }}</div>
    @endif

    <div class="glass-card kyc-card {{ $plain ? 'dqm-card' : '' }}">
    @if ($step === 'wallet')
        <div class="kyc-illo"><svg width="96" height="96" viewBox="0 0 96 96" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect x="14" y="26" width="68" height="46" rx="8"/><path d="M14 38h68"/><circle cx="66" cy="55" r="4" fill="currentColor"/></svg></div>
        @if (auth()->user()->virtual_account_number)
            <h1 class="kyc-title">Your wallet account is ready</h1>
            <p class="kyc-sub">{{ auth()->user()->virtual_account_bank }} · {{ auth()->user()->virtual_account_number }}</p>
            <a class="btn-primary w-full justify-center" href="{{ route('kyc.show', ['step' => $nextKey]) }}">Continue</a>
        @else
            <h1 class="kyc-title">Set up your wallet account</h1>
            <p class="kyc-sub">Add your phone number and BVN to get your own account number for deposits. This step is optional.</p>
            <form method="POST" action="{{ route('kyc.save', 'wallet') }}" class="kyc-form">
                @csrf
                <div>
                    <label class="text-xs" style="opacity:0.7;">Phone number</label>
                    <input type="tel" name="phonenumber" inputmode="tel" value="{{ old('phonenumber', $profile->phone) }}" placeholder="08012345678" class="glass-input">
                </div>
                <div>
                    <label class="text-xs" style="opacity:0.7;">BVN (11 digits)</label>
                    <input type="text" name="bvn" inputmode="numeric" maxlength="11" autocomplete="off" placeholder="12345678901" class="glass-input">
                </div>
                <button type="submit" class="btn-primary w-full justify-center">Save and continue</button>
            </form>
            <a class="kyc-skip" href="{{ route('kyc.show', ['step' => $nextKey]) }}">Skip for now</a>
            <p class="kyc-foot"><i data-lucide="lock" style="width:14px;height:14px"></i> Your BVN goes straight to Paystack to create your account number. We do not store it.</p>
        @endif

    @elseif ($step === 'id')
        <div class="kyc-illo"><svg width="96" height="96" viewBox="0 0 96 96" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect x="10" y="22" width="76" height="52" rx="8"/><circle cx="32" cy="46" r="8"/><path d="M20 64c2-7 8-10 12-10s10 3 12 10"/><path d="M54 40h22M54 50h22M54 60h14"/></svg></div>
        <h1 class="kyc-title">Upload your ID</h1>
        <p class="kyc-sub">Passport, national ID, driver licence, voter card or residence card. Photos must be clear. Optional.</p>
        <form method="POST" action="{{ route('kyc.save', 'id') }}" enctype="multipart/form-data" class="kyc-form">
            @csrf
            <div>
                <label class="text-xs" style="opacity:0.7;">Document type</label>
                <select name="id_type" class="glass-input">
                    <option value="">Choose a document</option>
                    @foreach ($idTypes as $k => $v)
                        <option value="{{ $k }}" {{ old('id_type', $profile->id_type) === $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-xs" style="opacity:0.7;">Front of ID</label>
                <label class="kyc-drop"><span class="kyc-fname">{{ $profile->id_front_path ? 'Uploaded. Tap to replace' : 'Choose photo' }}</span><i data-lucide="upload" style="width:18px;height:18px"></i><input type="file" name="id_front" accept="image/*" hidden onchange="kycName(this)"></label>
            </div>
            <div>
                <label class="text-xs" style="opacity:0.7;">Back of ID</label>
                <label class="kyc-drop"><span class="kyc-fname">{{ $profile->id_back_path ? 'Uploaded. Tap to replace' : 'Choose photo' }}</span><i data-lucide="upload" style="width:18px;height:18px"></i><input type="file" name="id_back" accept="image/*" hidden onchange="kycName(this)"></label>
            </div>
            <button type="submit" class="btn-primary w-full justify-center">Save and continue</button>
        </form>
        <a class="kyc-skip" href="{{ route('kyc.show', ['step' => $nextKey]) }}">Skip for now</a>
        <p class="kyc-foot"><i data-lucide="clock" style="width:14px;height:14px"></i> Most people finish this step in under 3 minutes.</p>

    @elseif ($step === 'selfie')
        <div class="kyc-illo"><svg width="96" height="96" viewBox="0 0 96 96" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><rect x="28" y="10" width="40" height="76" rx="8"/><circle cx="48" cy="44" r="12"/><path d="M42 48c2 3 10 3 12 0"/><path d="M42 72h12"/></svg></div>
        <h1 class="kyc-title">Take a selfie</h1>
        <p class="kyc-sub">The photo should be clear with your face fully inside the frame. Optional.</p>
        <form method="POST" action="{{ route('kyc.save', 'selfie') }}" enctype="multipart/form-data" class="kyc-form">
            @csrf
            <label class="kyc-drop"><span class="kyc-fname">{{ $profile->selfie_path ? 'Uploaded. Tap to retake' : 'Take a selfie' }}</span><i data-lucide="camera" style="width:18px;height:18px"></i><input type="file" name="selfie" accept="image/*" capture="user" hidden onchange="kycName(this)"></label>
            <button type="submit" class="btn-primary w-full justify-center">Save and continue</button>
        </form>
        <a class="kyc-skip" href="{{ route('kyc.show', ['step' => $nextKey]) }}">Skip for now</a>

    @elseif ($step === 'address')
        <div class="kyc-illo"><svg width="96" height="96" viewBox="0 0 96 96" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M48 84S22 58 22 38a26 26 0 0 1 52 0c0 20-26 46-26 46z"/><circle cx="48" cy="38" r="9"/></svg></div>
        <h1 class="kyc-title">Where do you live?</h1>
        <p class="kyc-sub">Your address helps us keep deliveries and payouts safe. Optional.</p>
        <form method="POST" action="{{ route('kyc.save', 'address') }}" class="kyc-form">
            @csrf
            <div>
                <label class="text-xs" style="opacity:0.7;">Street address</label>
                <input type="text" name="address_line" value="{{ old('address_line', $profile->address_line) }}" class="glass-input">
            </div>
            <div>
                <label class="text-xs" style="opacity:0.7;">City</label>
                <input type="text" name="city" value="{{ old('city', $profile->city) }}" class="glass-input">
            </div>
            <div>
                <label class="text-xs" style="opacity:0.7;">State</label>
                <input type="text" name="state" value="{{ old('state', $profile->state) }}" class="glass-input">
            </div>
            <div>
                <label class="text-xs" style="opacity:0.7;">Country</label>
                <input type="text" name="country" value="{{ old('country', $profile->country ?: 'Nigeria') }}" class="glass-input">
            </div>
            <button type="submit" class="btn-primary w-full justify-center">Finish</button>
        </form>
        <a class="kyc-skip" href="{{ route('kyc.show', ['step' => 'done']) }}">Skip for now</a>

    @else
        <div class="kyc-illo"><svg width="96" height="96" viewBox="0 0 96 96" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="48" cy="48" r="34"/><path d="M34 48l10 10 20-22"/></svg></div>
        <h1 class="kyc-title">{{ $profile->status === 'submitted' ? 'Under review' : 'Your details' }}</h1>
        <p class="kyc-sub">You can come back and add anything you skipped, whenever you like.</p>
        <ul class="kyc-list">
            @foreach ($labels as $key => $label)
                <li><span>{{ $label }}</span><span>{{ in_array($key, $done, true) ? '✓ Added' : 'Skipped' }}</span></li>
            @endforeach
        </ul>
        @if ($profile->status !== 'submitted' && ($profile->id_front_path || $profile->selfie_path))
            <form method="POST" action="{{ route('kyc.submit') }}">
                @csrf
                <button type="submit" class="btn-primary w-full justify-center">Submit for review</button>
            </form>
        @endif
        <a class="kyc-skip" href="{{ route('kyc.show', ['step' => 'wallet']) }}">Edit my details</a>
        <a class="kyc-skip" href="{{ route('dashboard') }}">Back to dashboard</a>
    @endif
    </div>
</main>
<script>
function kycName(i){var s=i.parentNode.querySelector('.kyc-fname');if(s&&i.files&&i.files[0]){s.textContent=i.files[0].name;}}
if(window.lucide){lucide.createIcons();}
</script>
@endsection
