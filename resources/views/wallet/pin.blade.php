@extends('layouts.app-authenticated')
@section('content')

<main class="max-w-md mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-1">Transaction PIN</h1>
    <p class="text-muted text-sm mb-6">
        @if($hasPin)
            Your PIN protects every purchase made from your wallet.
        @else
            Set a PIN to protect purchases made from your wallet — required before you can buy airtime, data, or cable.
        @endif
    </p>

    @if(session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-error mb-4">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('wallet.pin.update') }}" class="stat-card p-6">
        @csrf
        @if($hasPin)
            <label class="block text-sm mb-1">Current PIN</label>
            <input type="password" name="current_pin" inputmode="numeric" maxlength="6" required class="w-full mb-3 p-2 border rounded">
        @endif

        <label class="block text-sm mb-1">New PIN (4–6 digits)</label>
        <input type="password" name="pin" inputmode="numeric" maxlength="6" required class="w-full mb-3 p-2 border rounded">

        <label class="block text-sm mb-1">Confirm New PIN</label>
        <input type="password" name="pin_confirmation" inputmode="numeric" maxlength="6" required class="w-full mb-4 p-2 border rounded">

        <button type="submit" class="btn-primary w-full">{{ $hasPin ? 'Update PIN' : 'Set PIN' }}</button>
    </form>
</main>
@endsection
