<!DOCTYPE html>
<html lang="en" data-theme="dark"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Confirm it's you</title>
<style>
*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:20px;font-family:system-ui,sans-serif;color:#E8ECF8;background:radial-gradient(800px 500px at 70% -10%,rgba(80,92,255,.3),transparent 60%),radial-gradient(600px 400px at 0 100%,rgba(56,217,232,.18),transparent 60%),#05070D}
.c{width:100%;max-width:380px;padding:28px;border-radius:26px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);backdrop-filter:blur(20px);text-align:center}
.c h1{margin:12px 0 6px;font-size:22px}.c p{margin:0 0 18px;opacity:.7;font-size:14px}
.sh{width:56px;height:56px;margin:auto;border-radius:50%;display:grid;place-items:center;font-size:26px;background:linear-gradient(135deg,#5B63FF,#38D9E8)}
input{width:100%;padding:14px;border-radius:14px;border:1px solid rgba(255,255,255,.14);background:rgba(0,0,0,.35);color:#fff;font-size:16px;margin-bottom:12px}
button{width:100%;padding:14px;border:0;border-radius:14px;font-weight:700;font-size:15px;color:#05070D;background:#38D9E8}
.e{color:#ff6b6b;font-size:13px;margin:-4px 0 10px}.w{color:#ffd166;font-size:13px;margin-bottom:12px}
a{color:#9aa4c4;font-size:13px;display:inline-block;margin-top:14px}
</style></head><body>
<form class="c" method="POST" action="{{ route('admin.confirm.post') }}">
@csrf
<div class="sh">🔐</div><h1>Confirm it's you</h1><p>This action needs your password.</p>
@if(session('warn'))<div class="w">{{ session('warn') }}</div>@endif
<input type="password" name="password" placeholder="Your password" autocomplete="current-password" autofocus required>
@error('password')<div class="e">{{ $message }}</div>@enderror
<button type="submit">Unlock for 10 minutes</button>
<a href="{{ route('admin.index') }}">Cancel</a>
</form></body></html>
