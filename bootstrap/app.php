<?php

use App\Jobs\CheckLowStockAlert;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Console\Scheduling\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->job(new CheckLowStockAlert())
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->onOneServer()
            ->appendOutputTo(storage_path('logs/schedule.log'));
    })
    ->withMiddleware(function (Middleware $middleware): void {
        // Exclude the Stripe webhook from CSRF verification
        $middleware->validateCsrfTokens(except: [
            '/webhook/stripe',
        ]);

        // Append global security headers to every web response
        $middleware->web(append: [
            App\Http\Middleware\SecurityHeaders::class,
        ]);

        // Middleware aliases
        $middleware->alias([
            'validate.payment.screenshot' => App\Http\Middleware\ValidatePaymentScreenshot::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
