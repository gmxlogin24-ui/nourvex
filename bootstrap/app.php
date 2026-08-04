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
        ]);
        $middleware->appendToGroup('web', \App\Http\Middleware\TrackStreak::class);
        $middleware->alias(['signup.paid' => \App\Http\Middleware\EnsureSignupFeePaid::class]);
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
