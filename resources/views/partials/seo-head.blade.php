@hasSection('meta')
@yield('meta')
@else
<meta name="description" content="NAURVEX is a campus app to work, sell and get paid: escrow-held hustle payments, a student wallet, a campus market, airtime and data.">
@endif
<link rel="canonical" href="{{ url()->current() }}">
<meta property="og:site_name" content="NAURVEX">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="NAURVEX - @yield('title', 'Work, sell, get paid')">
<meta name="twitter:card" content="summary">
@php
$nvOrg = ['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => 'NAURVEX LTD', 'alternateName' => 'NAURVEX', 'url' => url('/')];
@endphp
<script type="application/ld+json">@json($nvOrg)</script>
@stack('head')
