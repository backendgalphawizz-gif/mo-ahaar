<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection (modern browsers ignore this, but good for legacy support)
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Referrer Policy - Control how much referrer information is shared
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy - Control which browser features can be used
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=(), usb=()');

        // Content Security Policy - Prevent XSS and data injection attacks
        $csp = "default-src 'self'; "
            . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://code.jquery.com; "
            . "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
            . "img-src 'self' data: https: blob:; "
            . "font-src 'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com data:; "
            . "connect-src 'self' https: wss:; "
            . "frame-ancestors 'self'; "
            . "base-uri 'self'; "
            . "form-action 'self';";
        $response->headers->set('Content-Security-Policy', $csp);

        // Strict Transport Security - Force HTTPS (only in production)
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}

