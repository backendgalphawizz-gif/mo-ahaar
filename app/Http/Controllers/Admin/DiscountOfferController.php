<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DiscountOfferRequest;
use App\Models\DiscountOffer;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class DiscountOfferController extends Controller
{
    // -------------------------------------------------------------------------
    // Index – list all offers
    // -------------------------------------------------------------------------
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));

        $offers = DiscountOffer::when($search !== '', fn ($q) => $q->where('title', 'like', '%' . $search . '%'))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.discount-offers.index', compact('offers', 'search'));
    }

    // -------------------------------------------------------------------------
    // Create form
    // -------------------------------------------------------------------------
    public function create()
    {
        $products   = Product::select('product_id', 'product_name')
            ->where('status', 1)
            ->orderBy('product_name')
            ->get();

        $categories = ProductCategory::select('category_id', 'category_name')
            ->orderBy('category_name')
            ->get();

        return view('admin.discount-offers.create', compact('products', 'categories'));
    }

    // -------------------------------------------------------------------------
    // Store
    // -------------------------------------------------------------------------
    public function store(DiscountOfferRequest $request)
    {
        DiscountOffer::create($request->validated());

        return redirect()->route('admin.discount-offers.index')
            ->with('success', 'Discount offer created successfully.');
    }

    // -------------------------------------------------------------------------
    // Show detail
    // -------------------------------------------------------------------------
    public function show(DiscountOffer $discountOffer)
    {
        $products   = collect();
        $categories = collect();

        if ($discountOffer->apply_to === DiscountOffer::APPLY_PRODUCTS && !empty($discountOffer->product_ids)) {
            $products = Product::whereIn('product_id', $discountOffer->product_ids)
                ->select('product_id', 'product_name')
                ->get();
        }

        if ($discountOffer->apply_to === DiscountOffer::APPLY_CATEGORIES && !empty($discountOffer->category_ids)) {
            $categories = ProductCategory::whereIn('category_id', $discountOffer->category_ids)
                ->select('category_id', 'category_name')
                ->get();
        }

        return view('admin.discount-offers.show', compact('discountOffer', 'products', 'categories'));
    }

    // -------------------------------------------------------------------------
    // Edit form
    // -------------------------------------------------------------------------
    public function edit(DiscountOffer $discountOffer)
    {
        $products   = Product::select('product_id', 'product_name')
            ->where('status', 1)
            ->orderBy('product_name')
            ->get();

        $categories = ProductCategory::select('category_id', 'category_name')
            ->orderBy('category_name')
            ->get();

        return view('admin.discount-offers.edit', compact('discountOffer', 'products', 'categories'));
    }

    // -------------------------------------------------------------------------
    // Update
    // -------------------------------------------------------------------------
    public function update(DiscountOfferRequest $request, DiscountOffer $discountOffer)
    {
        $discountOffer->update($request->validated());

        return redirect()->route('admin.discount-offers.index')
            ->with('success', 'Discount offer updated successfully.');
    }

    // -------------------------------------------------------------------------
    // Delete
    // -------------------------------------------------------------------------
    public function destroy(DiscountOffer $discountOffer)
    {
        $discountOffer->delete();

        return redirect()->route('admin.discount-offers.index')
            ->with('success', 'Discount offer deleted successfully.');
    }

    // -------------------------------------------------------------------------
    // AJAX toggle active status
    // -------------------------------------------------------------------------
    public function toggleStatus(Request $request, DiscountOffer $discountOffer)
    {
        $discountOffer->is_active = $discountOffer->is_active ? 0 : 1;
        $discountOffer->save();

        return response()->json([
            'status'    => true,
            'is_active' => $discountOffer->is_active,
            'message'   => $discountOffer->is_active ? 'Offer enabled.' : 'Offer disabled.',
        ]);
    }
}
