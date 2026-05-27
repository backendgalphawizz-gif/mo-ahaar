<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use Illuminate\Http\Request;

class ProductReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductReview::with(['product', 'customer.user'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by rating
        if ($request->filled('rating') && $request->rating !== '') {
            $query->where('rating', $request->rating);
        }

        // Search by product name or review text
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('product', function ($pq) use ($search) {
                    $pq->where('name', 'like', "%{$search}%");
                })->orWhere('review', 'like', "%{$search}%");
            });
        }

        $reviews = $query->paginate(20)->withQueryString();

        $totalCount    = ProductReview::count();
        $pendingCount  = ProductReview::where('status', 0)->count();
        $approvedCount = ProductReview::where('status', 1)->count();
        $rejectedCount = ProductReview::where('status', 2)->count();

        return view('admin.products.productReviews', compact(
            'reviews',
            'totalCount',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    public function updateStatus(Request $request, int $id)
    {
        $review = ProductReview::findOrFail($id);
        $review->status = (int) $request->input('status');
        $review->save();

        return redirect()->back()->with('success', 'Review status updated successfully.');
    }

    public function destroy(int $id)
    {
        $review = ProductReview::findOrFail($id);
        $review->delete();

        return redirect()->back()->with('success', 'Review deleted successfully.');
    }
}
