<?php

namespace App\Http\Middleware;

use App\Models\Users;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveCustomerUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user instanceof Users || !$user->isCustomerAppUser()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized customer access',
            ], 403);
        }

        $restriction = $user->apiAccessRestriction();
        if ($restriction !== null) {
            return response()->json([
                'status' => false,
                'message' => $restriction['message'],
                'data' => [
                    'account_status' => $restriction['account_status'],
                    'force_logout' => $restriction['force_logout'],
                ],
            ], $restriction['http_status']);
        }

        return $next($request);
    }
}
