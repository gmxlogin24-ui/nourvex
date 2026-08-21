<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Qelvo - @yield('title', 'Account')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="{{ asset('css/app-v2.css') }}?v={{ time() }}">
</head>
<body class="auth-body bg-gray-50 min-h-screen">
    <div class="auth-stage min-h-screen flex items-center justify-center p-4">
        <div class="auth-card-box w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
            <div class="auth-form-wrap" data-has-errors="{{ $errors->any() ? 'true' : 'false' }}">
                @yield('content')
            </div>
        </div>
    </div>
    @yield('scripts')
</body>
</html>
