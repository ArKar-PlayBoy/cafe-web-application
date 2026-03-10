<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        $csp = $this->getCspHeader();
        $response->headers->set('Content-Security-Policy', $csp);

        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        return $response;
    }

    private function getCspHeader(): string
    {
        $isLocal = app()->environment('local');
        $cacheKey = 'csp_header_' . ($isLocal ? 'local' : 'production');

        return Cache::remember($cacheKey, 3600, function () use ($isLocal) {
            return $this->buildCsp($isLocal);
        });
    }

    private function buildCsp(bool $isLocal): string
    {
        $scriptSrc = "'self' 'unsafe-inline' 'unsafe-eval' https://js.stripe.com https://cdn.jsdelivr.net";
        $styleSrc = "'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net";
        $connectSrc = "'self' https://api.stripe.com https://api.open-meteo.com";

        if ($isLocal) {
            $viteUrls = ' http://localhost:5173 http://127.0.0.1:5173 ws://localhost:5173 ws://127.0.0.1:5173 wss://localhost:5173 wss://127.0.0.1:5173';
            $scriptSrc .= $viteUrls;
            $styleSrc .= ' http://localhost:5173 http://127.0.0.1:5173';
            $connectSrc .= $viteUrls;
        }

        $formAction = "'self' https://checkout.stripe.com";
        if ($isLocal) {
            $formAction .= ' http://localhost:8000 http://127.0.0.1:8000';
        }

        return implode('; ', [
            "default-src 'self'",
            "script-src {$scriptSrc}",
            "style-src {$styleSrc}",
            "font-src 'self' https://fonts.gstatic.com data:",
            "img-src 'self' data: https:",
            "connect-src {$connectSrc}",
            'frame-src https://js.stripe.com https://hooks.stripe.com',
            "object-src 'none'",
            "base-uri 'self'",
            "form-action {$formAction}",
        ]);
    }
}
