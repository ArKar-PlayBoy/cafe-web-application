<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Exclude the Stripe webhook from CSRF verification
        $middleware->validateCsrfTokens(except: [
            '/webhook/stripe',
        ]);

        // Append global security headers to every web response
        $middleware->web(append: [
            App\Http\Middleware\SecurityHeaders::class,
        ]);

        // Append ban check to authenticated web routes
        $middleware->appendToGroup('web', [
            App\Http\Middleware\EnsureUserIsNotBanned::class,
        ]);

        // Middleware aliases
        $middleware->alias([
            'validate.payment.screenshot' => App\Http\Middleware\ValidatePaymentScreenshot::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
