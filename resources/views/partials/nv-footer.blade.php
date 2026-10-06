<footer class="nvp-foot">
    <div class="nvp-wrap nvp-foot-in">
        <nav aria-label="Footer">
            <a href="{{ route('welcome') }}">Home</a>
            <a href="{{ route('about') }}">About</a>
            <a href="{{ route('faq') }}">FAQ</a>
            <a href="{{ \Route::has('safety') ? route('safety') : url('/safety') }}">Safety</a>
            <a href="{{ \Route::has('news.index') ? route('news.index') : url('/news') }}">News</a>
            <a href="{{ route('register') }}">Create account</a>
            <a href="{{ route('login') }}">Log in</a>
        </nav>
        <div class="nvp-copy">&copy; {{ date('Y') }} NOURVEX LTD. All rights reserved.</div>
    </div>
</footer>
