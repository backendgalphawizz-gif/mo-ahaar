<?php

namespace App\Http\Controllers\API\CustomerApp;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Customers;
use App\Models\DiscountOffer;
use App\Models\Product;
use App\Models\Users;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private const CUSTOMER_ROLE_TYPE = Users::CUSTOMER_APP_ROLE_TYPE;

    // -----------------------------------------------------------------------
    // GET /api/customer-app/cart
    // View cart with line totals and summary
    // -----------------------------------------------------------------------
    public function index(Request $request)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $customer = $this->resolveCustomer($user);
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Cart retrieved successfully',
            'data'    => $this->buildCartPayloadForCustomer((int) $customer->customer_id),
        ]);
    }

    // -----------------------------------------------------------------------
    // POST /api/customer-app/cart/add
    // Add product to cart or increase quantity if already present
    // Body: { product_id, quantity (optional, default 1) }
    // -----------------------------------------------------------------------
    public function add(Request $request)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $validated = $request->validate([
            'product_id' => 'required|integer',
            'quantity'   => 'sometimes|integer|min:1|max:100',
        ]);

        $customer = $this->resolveCustomer($user);
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        $product = Product::query()
            ->visibleToCustomerUser($user)
            ->where('product_id', $validated['product_id'])
            ->whereIn('status', [1, '1'])
            ->whereIn('is_active_status', [1, '1'])
            ->first();

        if (!$product) {
            return response()->json(['status' => false, 'message' => 'Product not found or unavailable'], 404);
        }

        $qty = (int) ($validated['quantity'] ?? 1);
        $minOrder = $this->minimumOrderQuantityForProduct($product);

        if ($qty < $minOrder) {
            return response()->json([
                'status'  => false,
                'message' => 'Minimum order quantity for this product is ' . $minOrder,
            ], 422);
        }

        // Check stock
        if ($product->stock !== null && $product->stock < $qty) {
            return response()->json(['status' => false, 'message' => 'Insufficient stock'], 422);
        }

        $cartItem = CartItem::where('customer_id', $customer->customer_id)
            ->where('product_id', $product->product_id)
            ->first();

        if ($cartItem) {
            $newQty = $cartItem->quantity + $qty;
            if ($product->stock !== null && $product->stock < $newQty) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Only ' . $product->stock . ' units available in stock',
                    'data'    => [
                        'available_stock' => (int) $product->stock,
                        'current_quantity' => (int) $cartItem->quantity,
                    ],
                ], 422);
            }
            if ($newQty < $minOrder) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Minimum order quantity is ' . $minOrder . '. After adding, the cart line would be ' . $newQty . '.',
                ], 422);
            }
            $cartItem->quantity = $newQty;
            $cartItem->unit_price = $product->price;
            $cartItem->sale_price = $product->sale_price ?: null;
            $cartItem->save();
        } else {
            $cartItem = CartItem::create([
                'customer_id' => $customer->customer_id,
                'product_id'  => $product->product_id,
                'quantity'    => $qty,
                'unit_price'  => $product->price,
                'sale_price'  => $product->sale_price ?: null,
            ]);
        }

        $cartItem->load('product');

        return response()->json([
            'status'  => true,
            'message' => 'Product added to cart',
            'data'    => [
                'item' => $this->formatCartItem($cartItem),
                'cart' => $this->buildCartPayloadForCustomer((int) $customer->customer_id),
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // POST /api/customer-app/cart/update
    // Set exact quantity for a cart item
    // Body: { product_id, quantity }
    // -----------------------------------------------------------------------
    public function update(Request $request)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $validated = $request->validate([
            'cart_item_id' => 'nullable|integer|required_without:product_id',
            'product_id' => 'nullable|integer|required_without:cart_item_id',
            'quantity'   => 'required|integer|min:1|max:100',
        ]);

        $customer = $this->resolveCustomer($user);
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        $cartItem = $this->findCartItemForCustomer((int) $customer->customer_id, $validated);

        if (!$cartItem) {
            return response()->json(['status' => false, 'message' => 'Item not found in cart'], 404);
        }

        $product = Product::query()
            ->visibleToCustomerUser($user)
            ->where('product_id', $cartItem->product_id)
            ->whereIn('status', [1, '1'])
            ->whereIn('is_active_status', [1, '1'])
            ->first();

        if (!$product) {
            return response()->json(['status' => false, 'message' => 'Product not found or unavailable'], 404);
        }

        if ($product->stock !== null && $product->stock < $validated['quantity']) {
            return response()->json([
                'status'  => false,
                'message' => 'Only ' . $product->stock . ' units available in stock',
            ], 422);
        }

        $minOrder = $this->minimumOrderQuantityForProduct($product);
        if ((int) $validated['quantity'] < $minOrder) {
            return response()->json([
                'status'  => false,
                'message' => 'Minimum order quantity for this product is ' . $minOrder,
            ], 422);
        }

        $cartItem->quantity = $validated['quantity'];
        $cartItem->unit_price = $product->price;
        $cartItem->sale_price = $product->sale_price ?: null;
        $cartItem->save();
        $cartItem->load('product');

        return response()->json([
            'status'  => true,
            'message' => 'Cart item updated',
            'data'    => [
                'item' => $this->formatCartItem($cartItem),
                'cart' => $this->buildCartPayloadForCustomer((int) $customer->customer_id),
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // POST /api/customer-app/cart/remove
    // Remove a single product from cart
    // Body: { product_id }
    // -----------------------------------------------------------------------
    public function remove(Request $request)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $validated = $request->validate([
            'cart_item_id' => 'nullable|integer|required_without:product_id',
            'product_id' => 'nullable|integer|required_without:cart_item_id',
        ]);

        $customer = $this->resolveCustomer($user);
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        $cartItem = $this->findCartItemForCustomer((int) $customer->customer_id, $validated);

        if (!$cartItem) {
            return response()->json(['status' => false, 'message' => 'Item not found in cart'], 404);
        }

        CartItem::where('cart_item_id', '=', $cartItem->cart_item_id)->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Item removed from cart',
            'data'    => [
                'cart' => $this->buildCartPayloadForCustomer((int) $customer->customer_id),
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // POST /api/customer-app/cart/clear
    // Empty the entire cart
    // -----------------------------------------------------------------------
    public function clear(Request $request)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $customer = $this->resolveCustomer($user);
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        CartItem::where('customer_id', $customer->customer_id)->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Cart cleared',
            'data'    => [
                'cart' => $this->buildCartPayloadForCustomer((int) $customer->customer_id),
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------
    /**
     * Wholesaler products may require a minimum line quantity; others default to 1.
     */
    private function minimumOrderQuantityForProduct(Product $product): int
    {
        if ($product->target_user_type !== Product::TARGET_WHOLESALER) {
            return 1;
        }

        $min = (int) ($product->min_quantity ?? 0);

        return $min >= 1 ? $min : 1;
    }

    private function resolveCustomer(Users $user): ?Customers
    {
        return Customers::where('user_id', '=', $user->user_id)->first();
    }

    private function findCartItemForCustomer(int $customerId, array $validated): ?CartItem
    {
        return CartItem::with('product')
            ->where('customer_id', $customerId)
            ->when(
                !empty($validated['cart_item_id']),
                fn ($query) => $query->where('cart_item_id', (int) $validated['cart_item_id']),
                fn ($query) => $query->where('product_id', (int) $validated['product_id'])
            )
            ->first();
    }

    private function buildCartPayloadForCustomer(int $customerId): array
    {
        $items = CartItem::with('product')
            ->where('customer_id', $customerId)
            ->get();

        return $this->buildCartResponse($items);
    }

    private function buildCartResponse($items): array
    {
        // Load all currently valid & active offers once
        $offers = DiscountOffer::active()->currentlyValid()->get();

        $cartItems = $items->map(fn (CartItem $item) => $this->formatCartItem($item))->values();

        $subtotal = $cartItems->sum(fn ($i) => (float) $i['line_total']);

        // --- Apply per-line discounts (quantity-based, product/category scoped) ---
        $totalOfferDiscount = 0.0;
        $appliedOffers      = [];

        foreach ($items as $item) {
            $product  = $item->product;
            if (!$product) {
                continue;
            }

            $unitPrice  = (float) ($item->sale_price ?: $item->unit_price);
            $qty        = (int) $item->quantity;
            $categoryId = $product->category_id ? (int) $product->category_id : null;

            foreach ($offers as $offer) {
                if (!$offer->appliesToProduct((int) $product->product_id, $categoryId)) {
                    continue;
                }

                $lineDiscount = $offer->calculateLineDiscount($qty, $unitPrice);
                if ($lineDiscount <= 0) {
                    continue;
                }

                $totalOfferDiscount += $lineDiscount;
                $appliedOffers[$offer->id] = [
                    'offer_id'   => $offer->id,
                    'title'      => $offer->title,
                    'type'       => $offer->discount_type,
                    'value'      => number_format((float) $offer->discount_value, 2, '.', ''),
                ];
            }
        }

        // --- Apply cart-amount conditions (after per-line discounts were summed) ---
        // Any offer that passes cart-amount gate but didn't already produce per-line
        // discount (e.g. "all products" offers) are also evaluated here.
        foreach ($offers as $offer) {
            if (!$offer->cartAmountConditionMet($subtotal)) {
                // Remove from applied if already counted
                unset($appliedOffers[$offer->id]);
            }
        }

        // Recalculate after filtering out cart-amount-failed offers
        $totalOfferDiscount = 0.0;
        foreach ($items as $item) {
            $product = $item->product;
            if (!$product) {
                continue;
            }
            $unitPrice  = (float) ($item->sale_price ?: $item->unit_price);
            $qty        = (int) $item->quantity;
            $categoryId = $product->category_id ? (int) $product->category_id : null;

            foreach ($offers as $offer) {
                if (!isset($appliedOffers[$offer->id])) {
                    continue;
                }
                if (!$offer->appliesToProduct((int) $product->product_id, $categoryId)) {
                    continue;
                }
                $lineDiscount = $offer->calculateLineDiscount($qty, $unitPrice);
                $totalOfferDiscount += $lineDiscount;
            }
        }

        $grandTotal = max(0, $subtotal - $totalOfferDiscount);

        return [
            'items'               => $cartItems,
            'items_count'         => $cartItems->count(),
            'subtotal'            => number_format($subtotal, 2, '.', ''),
            'offer_discount'      => number_format($totalOfferDiscount, 2, '.', ''),
            'grand_total'         => number_format($grandTotal, 2, '.', ''),
            'applied_offers'      => array_values($appliedOffers),
        ];
    }

    private function formatCartItem(CartItem $item): array
    {
        $product = $item->product;
        $unitPrice = (float) ($item->sale_price ?: $item->unit_price);
        $lineTotal = $unitPrice * $item->quantity;

        return [
            'cart_item_id' => $item->cart_item_id,
            'product_id' => $item->product_id,
            'product_name' => $product ? $product->product_name : null,
            'product_image_url' => ($product && !empty($product->product_image))
                ? url('public/uploads/products/' . $product->product_image)
                : null,
            'quantity' => $item->quantity,
            'unit_price' => number_format((float) $item->unit_price, 2, '.', ''),
            'sale_price' => $item->sale_price ? number_format((float) $item->sale_price, 2, '.', '') : null,
            'effective_price' => number_format($unitPrice, 2, '.', ''),
            'line_total' => number_format($lineTotal, 2, '.', ''),
            'minimum_quantity' => $product ? $this->minimumOrderQuantityForProduct($product) : 1,
        ];
    }

    private function isAuthorizedCustomer(?Users $user): bool
    {
        return $user && (int) $user->role_type === self::CUSTOMER_ROLE_TYPE;
    }
}
