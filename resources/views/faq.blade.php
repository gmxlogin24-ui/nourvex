@extends('layouts.landing')
@section('title', 'FAQ')
@section('meta')
<meta name="description" content="Answers about NOURVEX: hustles, escrow payments, your wallet and PIN, the Campus Market, airtime and data, levels and badges, and getting help.">
@endsection
@php
$faqs = [
    ['What is NOURVEX?', 'NOURVEX is a campus app where students can work, sell and get paid in one place. It brings hustles, a wallet, a campus market, a live campus feed, airtime and data, and an AI concierge together. It is run by NOURVEX LTD.'],
    ['Is it free to join?', 'Yes. Creating an account is free.'],
    ['What is a hustle?', 'A hustle is a task one student posts and another student does. The poster sets the budget, and anyone can browse open hustles and apply.'],
    ['How does escrow protect my payment?', 'When a hustle is accepted, the funds move into escrow. They are released to the person who did the work only after the poster marks it as delivered, so neither side is left exposed.'],
    ['How do I add money to my wallet?', 'You can top up by card or transfer. Every credit and debit shows up in your wallet as it happens.'],
    ['What is my PIN for?', 'Your PIN confirms anything that moves money, such as paying for a hustle or buying airtime, so nobody can spend from your wallet without it.'],
    ['How does the Campus Market work?', 'Sellers list textbooks, gadgets, services or rentals. Buyers confirm delivery before the funds are released to the seller. If something goes wrong, support is one tap away.'],
    ['Can I buy airtime and data?', 'Yes. You can buy airtime and data bundles for any network, paid straight from your NOURVEX wallet and confirmed with your PIN.'],
    ['What are levels and badges?', 'Every hustle you finish and every good review you earn pushes your level up. Higher levels unlock badges that buyers and sellers can see before they deal with you.'],
    ['Who is Levi?', 'Levi is the NOURVEX AI concierge. Ask in plain chat to find a hustle, check a wallet transaction or explain how something works.'],
    ['What if something goes wrong?', 'Use the support option in the app. Customer care and dispute help are there for hustles and Campus Market orders.'],
];
$ld = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => array_map(function ($f) {
        return ['@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]]];
    }, $faqs),
];
@endphp
@section('content')
<link rel="stylesheet" href="{{ asset('css/nv-pages.css') }}?v={{ @filemtime(public_path('css/nv-pages.css')) }}">
<main class="lp nvp">
    <div class="nvp-wrap">
        <header class="nvp-head">
            <p class="nvp-eyebrow">FAQ</p>
            <h1>Frequently asked questions</h1>
            <p class="nvp-lead">Quick answers about hustles, your wallet, payments and your account.</p>
        </header>

        <section class="nvp-sec">
            <div class="nvp-faq">
                @foreach ($faqs as $f)
                <details class="nvp-q">
                    <summary>{{ $f[0] }}</summary>
                    <p>{{ $f[1] }}</p>
                </details>
                @endforeach
            </div>
        </section>

        <section class="nvp-sec">
            <div class="nvp-actions">
                <a class="nvp-cta" href="{{ route('register') }}">Create your free account</a>
                <a class="nvp-link" href="{{ route('about') }}">About NOURVEX</a>
            </div>
        </section>
    </div>
    <script type="application/ld+json">@json($ld)</script>
    @include('partials.nv-footer')
</main>
@endsection
