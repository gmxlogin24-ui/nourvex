<link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=2">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32.png') }}?v=2">
<link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16.png') }}?v=2">
<link rel="apple-touch-icon" href="{{ asset('images/apple-touch-icon.png') }}?v=2">
{{-- K logo for the CSS logo badge; the favicons partial is loaded by every layout --}}
<link rel="stylesheet" href="{{ asset('css/logo-k.css') }}?v={{ @filemtime(public_path('css/logo-k.css')) }}">
