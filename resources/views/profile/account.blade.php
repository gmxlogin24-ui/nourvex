@extends('layouts.world-shell')

@section('world', 'profile')
@section('title', 'Account Details')

@section('content')
<link rel="stylesheet" href="{{ asset('css/profile-redesign.css') }}?v=1">
<link rel="stylesheet" href="{{ asset('css/world-pages.css') }}?v={{ @filemtime(public_path('css/world-pages.css')) }}">

<style>
  .pf2-back { display:inline-flex; align-items:center; gap:6px; color:var(--mute); font-size:12.5px; font-weight:600; text-decoration:none; margin-bottom:14px; }
  .pf2-back svg { width:15px; height:15px; }
  .pf2-h1 { font-size:21px; font-weight:800; letter-spacing:-.02em; margin:0; color:var(--ink); }
  .pf2-sub { font-size:12px; color:var(--mute); margin:3px 0 16px; }
  .pf2-card { background:#fff; border-radius:18px; padding:16px; box-shadow:var(--sh); margin-bottom:10px; }
  .pf2-card h2 { font-size:13px; font-weight:800; color:var(--ink); margin:0 0 12px; }
  .pf2-avatar-row { display:flex; align-items:center; gap:12px; margin-bottom:16px; }
  .pf2-av { width:54px; height:54px; border-radius:50%; overflow:hidden; display:flex; align-items:center; justify-content:center; flex-shrink:0;
    background:linear-gradient(135deg, var(--w), var(--w-dark)); color:#fff; font-size:20px; font-weight:800; }
  .pf2-av img { width:100%; height:100%; object-fit:cover; }
  .pf2-field { margin-bottom:12px; }
  .pf2-field label { display:block; font-size:11px; font-weight:700; color:var(--mute); margin-bottom:5px; }
  .pf2-field input { width:100%; height:42px; border-radius:12px; border:0; background:#F6F7FA; color:var(--ink); padding:0 13px; font-size:13.5px; outline:none; box-shadow:var(--sh-in); }
  .pf2-field input:focus { box-shadow:0 0 0 3px var(--w-tint), var(--sh-in); }
  .pf2-btn { display:inline-block; padding:11px 20px; border-radius:12px; border:0; background:var(--w); color:#fff !important; font:inherit; font-size:13px; font-weight:800; cursor:pointer; text-align:center; text-decoration:none; box-shadow:0 8px 16px -8px var(--w); }
  .pf2-flash { padding:10px 14px; border-radius:14px; font-size:12px; font-weight:600; margin-bottom:14px; }
  .pf2-flash.ok { background:var(--w-tint); color:var(--w-dark); }
  .pf2-flash.err { background:#FDE8E8; color:#C0392B; }
</style>

<div class="wp wp-profile">
  <a href="{{ route('profile.index') }}" class="pf2-back"><i data-lucide="arrow-left"></i> Profile</a>
  <h1 class="pf2-h1">Account Details</h1>
  <p class="pf2-sub">Your name, email, and password</p>

  @if (session('status'))
  <div class="pf2-flash ok">{{ session('status') }}</div>
  @endif
  @if ($errors->any())
  <div class="pf2-flash err">{{ $errors->first() }}</div>
  @endif

  <div class="pf2-card">
    <div class="pf2-avatar-row">
      <div class="pf2-av">
        @if($user->avatar_url)<img src="{{ $user->avatar_url }}">@else{{ strtoupper(substr($user->name, 0, 1)) }}@endif
      </div>
      <div>
        <p style="font-weight:800; font-size:14px; margin:0;">{{ $user->name }}</p>
        <p style="font-size:11.5px; color:var(--mute); margin:2px 0 0;">{{ $user->email }}</p>
      </div>
    </div>
    <h2>Basic Info</h2>
    <form method="POST" action="{{ route('profile.update') }}">
      @csrf
      <div class="pf2-field">
        <label>Name</label>
        <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
      </div>
      <div class="pf2-field">
        <label>Email</label>
        <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
        <label for="acUser" style="display:block;margin-top:14px;font-weight:700">Username</label>
        <input id="acUser" type="text" name="username" value="{{ old('username', $user->username) }}" maxlength="20" autocapitalize="none" autocomplete="off" placeholder="e.g. emmanuel_k">
        <small style="display:block;margin-top:4px;opacity:.65">3 to 20 characters: lowercase letters, numbers and underscores. This is what people see.</small>
        @error('username')<small style="display:block;margin-top:4px;color:#DC2626;font-weight:700">{{ $message }}</small>@enderror
        <label for="acBio" style="display:block;margin-top:14px;font-weight:700">Bio</label>
        <textarea id="acBio" name="bio" rows="3" maxlength="300" placeholder="Tell people what you do" style="width:100%">{{ old('bio', $user->bio) }}</textarea>
        @error('bio')<small style="display:block;margin-top:4px;color:#DC2626;font-weight:700">{{ $message }}</small>@enderror
      </div>
      <button type="submit" class="pf2-btn">Save Changes</button>
    </form>
  </div>

  <div class="pf2-card">
    <h2>Change Password</h2>
    <form method="POST" action="{{ route('profile.password') }}">
      @csrf
      <div class="pf2-field">
        <label>Current Password</label>
        <input type="password" name="current_password" required>
      </div>
      <div class="pf2-field">
        <label>New Password</label>
        <input type="password" name="password" required>
      </div>
      <div class="pf2-field">
        <label>Confirm New Password</label>
        <input type="password" name="password_confirmation" required>
      </div>
      <button type="submit" class="pf2-btn">Update Password</button>
    </form>
  </div>
</div>
@endsection
