<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InjectBearerTokenFromInput
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->bearerToken()) {
            $rawToken = $request->input('token') ?? $request->input('access_token');

            if (is_string($rawToken) && trim($rawToken) !== '') {
                $request->headers->set('Authorization', 'Bearer ' . trim($rawToken));
            }
        }

        return $next($request);
    }
}
