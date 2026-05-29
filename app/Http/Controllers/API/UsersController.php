<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Users;

class UsersController extends Controller
{
    /**
     * Get all users with name and email only
     */
    public function index()
    {
        try {
            $users = Users::select('user_id', 'name', 'email')->get();

            return response()->json([
                'status' => true,
                'message' => 'Users loaded successfully',
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Unable to load users. Please try again',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
