<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>{{ $game['title'] }}</title>
<style>
html,body{margin:0;height:100%;background:#07070c;color:#fff;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif}
.pl{display:flex;flex-direction:column;height:100vh;height:100dvh}
.pl-bar{display:flex;align-items:center;gap:12px;padding:calc(env(safe-area-inset-top,0px) + 8px) 12px 8px;background:#0d0d14;border-bottom:1px solid rgba(255,255,255,.08)}
.pl-bar a,.pl-bar button{height:38px;padding:0 16px;border-radius:99px;border:1px solid rgba(255,255,255,.18);background:rgba(255,255,255,.08);color:#fff;font:inherit;font-weight:700;font-size:14px;text-decoration:none;display:inline-flex;align-items:center;cursor:pointer}
.pl-bar b{flex:1;text-align:center;font-size:16px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.pl-frame{flex:1;width:100%;border:0;background:#000}
</style>
</head>
<body>
<div class="pl" id="pl">
  <div class="pl-bar">
    <a href="{{ route('games.index') }}">&larr; Games</a>
    <b>{{ $game['title'] }}</b>
    <button type="button" id="plFs">Full screen</button>
  </div>
  @if($local)
    <iframe class="pl-frame" id="plf" src="{{ $src }}" title="{{ $game['title'] }}" sandbox="allow-scripts allow-pointer-lock allow-forms" allow="fullscreen; autoplay; gamepad" referrerpolicy="no-referrer"></iframe>
  @else
    <iframe class="pl-frame" id="plf" src="{{ $src }}" title="{{ $game['title'] }}" sandbox="allow-scripts allow-same-origin allow-popups allow-forms allow-pointer-lock" allow="fullscreen; autoplay; gamepad" referrerpolicy="no-referrer"></iframe>
  @endif
</div>
<script>
document.getElementById('plFs').addEventListener('click', function () {
  var f = document.getElementById('plf');
  var r = f.requestFullscreen || f.webkitRequestFullscreen;
  if (r) r.call(f);
});
</script>
</body>
</html>
