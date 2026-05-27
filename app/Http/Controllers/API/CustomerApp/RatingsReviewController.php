<?php

namespace App\Http\Controllers\API\CustomerApp;

use App\Http\Controllers\Controller;
use App\Models\Customers;
use App\Models\PlatformFeedback;
use App\Models\Users;
use Illuminate\Http\Request;

class RatingsReviewController extends Controller
{
    private const CUSTOMER_ROLE_TYPE = 3;

    /**
     * POST /api/customer-app/feedback
     * Submit platform feedback
     */
    public function submitFeedback(Request $request)
    {
        /** @var Users|null $user */
        $user = $request->user();

        if (!$user || (int) $user->role_type !== self::CUSTOMER_ROLE_TYPE) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized customer access',
            ], 403);
        }

        $validated = $request->validate([
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'category' => ['nullable', 'string', 'max:100'],
            'feedback_text' => ['required', 'string', 'min:10', 'max:5000'],
        ]);

        $customer = Customers::where('user_id', $user->user_id)->first();
        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Customer profile not found',
            ], 404);
        }

        $feedback = PlatformFeedback::create([
            'customer_id' => $customer->customer_id,
            'user_id' => $user->user_id,
            'rating' => $validated['rating'] ?? null,
            'category' => $validated['category'] ?? null,
            'feedback_text' => $validated['feedback_text'],
            'status' => 1,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Feedback submitted successfully',
            'data' => [
                'feedback_id' => $feedback->feedback_id,
                'rating' => $feedback->rating,
                'category' => $feedback->category,
                'submitted_at' => $feedback->created_at->toDateTimeString(),
            ],
        ], 201);
    }

    /**
     * GET /api/customer-app/feedback
     * Get customer's own feedback submissions with pagination
     */
    public function getFeedback(Request $request)
    {
        /** @var Users|null $user */
        $user = $request->user();

        if (!$user || (int) $user->role_type !== self::CUSTOMER_ROLE_TYPE) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized customer access',
            ], 403);
        }

        $customer = Customers::where('user_id', $user->user_id)->first();
        if (!$customer) {
            return response()->json([
                'status' => false,
                'message' => 'Customer profile not found',
            ], 404);
        }

        $perPage = (int) $request->get('per_page', 10);
        $perPage = min($perPage, 100);

        $category = $request->get('category');

        $query = PlatformFeedback::where('customer_id', $customer->customer_id);

        if ($category) {
            $query->where('category', $category);
        }

        $feedback = $query
            ->orderByDesc('feedback_id')
            ->paginate($perPage);

        $feedbackData = $feedback->map(function ($item) {
            return [
                'feedback_id' => $item->feedback_id,
                'rating' => $item->rating,
                'category' => $item->category,
                'feedback_text' => $item->feedback_text,
                'status' => (int) $item->status,
                'submitted_at' => $item->created_at->toDateTimeString(),
                'updated_at' => $item->updated_at ? $item->updated_at->toDateTimeString() : null,
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Feedback retrieved successfully',
            'data' => [
                'feedback' => $feedbackData,
                'pagination' => [
                    'current_page' => $feedback->currentPage(),
                    'total' => $feedback->total(),
                    'per_page' => $feedback->perPage(),
                    'last_page' => $feedback->lastPage(),
                ],
            ],
        ], 200);
    }
}
