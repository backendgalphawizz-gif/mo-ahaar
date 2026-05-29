<?php

namespace App\Http\Controllers\API\Concerns;

use App\Models\Users;
use Illuminate\Http\JsonResponse;

trait RespondsWithAccountRestrictions
{
    protected function denyCustomerOrder(Users $user): ?JsonResponse
    {
        $restriction = $user->customerOrderRestriction();
        if ($restriction === null) {
            return null;
        }

        return response()->json([
            'status' => false,
            'message' => $restriction['message'],
            'data' => [
                'account_status' => $restriction['account_status'],
                'can_place_orders' => false,
                'force_logout' => $restriction['force_logout'],
            ],
        ], $restriction['http_status']);
    }
}
