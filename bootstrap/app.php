<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tier' => \App\Http\Middleware\RequireTier::class,
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'signup.paid' => \App\Http\Middleware\EnsureSignupFeePaid::class,
            'customer_care' => \App\Http\Middleware\IsCustomerCareOrAdmin::class,
        ]);
        // Trust ngrok (and any reverse proxy) so HTTPS/host headers are read correctly -
        // without this, requests through the tunnel can be misread as plain HTTP, breaking
        // secure cookies and generating wrong (http://) links/redirects.
        $middleware->trustProxies(at: '*');
        $middleware->appendToGroup('web', \App\Http\Middleware\TrackStreak::class);
        $middleware->validateCsrfTokens(except: [
            'webhooks/flutterwave',
        ]);
        $middleware->redirectGuestsTo(fn (Request $request) => route('plans.choose'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
