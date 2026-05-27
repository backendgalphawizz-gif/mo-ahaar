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
                'message' => 'Users retrieved successfully',
                'data' => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving users',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
