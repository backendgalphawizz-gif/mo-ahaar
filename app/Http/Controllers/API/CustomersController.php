<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customers;

class CustomersController extends Controller
{
    /**
     * Get all customers with all fields
     */
    public function index()
    {
        try {
            $customers = Customers::all();

            return response()->json([
                'status' => true,
                'message' => 'Customers retrieved successfully',
                'data' => $customers
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error retrieving customers',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
