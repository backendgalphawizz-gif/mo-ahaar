<?php

namespace App\Http\Middleware;

use App\Models\Users;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDriverUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user instanceof Users || !$user->isDriverAppUser()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized driver access',
            ], 403);
        }

        if ((int) $user->status !== 1) {
            return response()->json([
                'status' => false,
                'message' => 'Your driver account is inactive. Please contact support.',
            ], 403);
        }

        return $next($request);
    }
}
