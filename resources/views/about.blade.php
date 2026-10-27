@extends('layouts.landing')
@section('title', 'About us')
@section('meta')
<meta name="description" content="NAURVEX LTD builds a campus app where students work, sell and get paid safely: escrow-held hustles, a student wallet, a campus market, airtime and data.">
@endsection
@section('content')
<link rel="stylesheet" href="{{ asset('css/nv-pages.css') }}?v={{ @filemtime(public_path('css/nv-pages.css')) }}">
<main class="lp nvp">
    <div class="nvp-wrap">
        <header class="nvp-head">
            <p class="nvp-eyebrow">About NAURVEX</p>
            <h1>Work, sell and get paid, all in one campus app.</h1>
            <p class="nvp-lead">NAURVEX LTD builds tools that help students earn, buy, sell and pay safely, without juggling five different apps.</p>
        </header>

        <section class="nvp-sec">
            <h2>What you can do on NAURVEX</h2>
            <div class="nvp-grid">
                <div class="nvp-card"><h3>Hustles</h3><p>Post a task with your budget, or browse open ones and apply. Pay is held in escrow until the work is delivered.</p></div>
                <div class="nvp-card"><h3>Wallet</h3><p>One balance for everything. Top up by card or transfer, track every credit and debit, and confirm payments with your own PIN.</p></div>
                <div class="nvp-card"><h3>Campus Market</h3><p>Buy and sell textbooks, gadgets, services and rentals with other students.</p></div>
                <div class="nvp-card"><h3>Pulse</h3><p>A live feed of campus announcements, buzz and updates from the people you follow.</p></div>
                <div class="nvp-card"><h3>Airtime and data</h3><p>Buy airtime and data for any network, straight from your wallet.</p></div>
                <div class="nvp-card"><h3>Levels and badges</h3><p>Finish hustles and earn good reviews to level up and unlock badges people can see.</p></div>
                <div class="nvp-card"><h3>Levi, your AI concierge</h3><p>Ask in plain chat to find a hustle, check a transaction or learn how something works.</p></div>
            </div>
        </section>

        <section class="nvp-sec">
            <h2>How payments stay safe</h2>
            <div class="nvp-box">
                <p>When a hustle is accepted, the money moves into escrow. It is released to the person doing the work only after the poster confirms delivery. On the Campus Market, buyers confirm delivery before funds are released.</p>
                <p>Anything that moves money needs your own PIN, and support is one tap away if something goes wrong.</p>
                <a class="nvp-link" href="{{ \Route::has('safety') ? route('safety') : url('/safety') }}">Read more about safety</a>
            </div>
        </section>

        <section class="nvp-sec">
            <div class="nvp-actions">
                <a class="nvp-cta" href="{{ route('register') }}">Create your free account</a>
                <a class="nvp-link" href="{{ route('faq') }}">Read the FAQ</a>
            </div>
        </section>
    </div>
    @include('partials.nv-footer')
</main>
@endsection
