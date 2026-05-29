<?php

namespace App\Http\Controllers\API\CustomerApp;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\CustomerAddress;
use App\Models\Customers;
use App\Models\DiscountOffer;
use App\Models\Product;
use App\Models\Users;
use App\Services\CustomerPromoResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

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

        $productVendorId = $this->productVendorId($product);
        $cartReplaced = false;

        if ($productVendorId !== null) {
            $existingVendorId = $this->resolveCartVendorId((int) $customer->customer_id);
            if (
                $existingVendorId !== null
                && $existingVendorId !== $productVendorId
                && $this->customerHasCartItems((int) $customer->customer_id)
            ) {
                $this->clearCartForCustomer($customer, true);
                $cartReplaced = true;
            }
        }

        $cartItem = CartItem::where('customer_id', $customer->customer_id)
            ->where('product_id', $product->product_id)
            ->first();

        if ($cartItem) {
            $newQty = $cartItem->quantity + $qty;
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

        if ($productVendorId !== null && Schema::hasColumn('customers', 'active_cart_vendor_id')) {
            $customer->active_cart_vendor_id = $productVendorId;
            $customer->save();
        }

        $cartItem->load('product');

        return response()->json([
            'status'  => true,
            'message' => $cartReplaced
                ? 'Previous restaurant cart cleared. Product added to cart.'
                : 'Product added to cart',
            'data'    => [
                'cart_replaced' => $cartReplaced,
                'active_vendor_id' => $productVendorId,
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
            ->where('product_id', $cartItem->product_id)
            ->whereIn('status', [1, '1'])
            ->whereIn('is_active_status', [1, '1'])
            ->first();

        if (!$product) {
            return response()->json(['status' => false, 'message' => 'Product not found or unavailable'], 404);
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

        $this->clearCartForCustomer($customer, true);

        return response()->json([
            'status'  => true,
            'message' => 'Cart cleared',
            'data'    => [
                'cart' => $this->buildCartPayloadForCustomer((int) $customer->customer_id),
            ],
        ]);
    }

    public function updateCookingInstructions(Request $request)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $validated = $request->validate([
            'cooking_instructions' => ['nullable', 'string', 'max:1000'],
            'instructions' => ['nullable', 'string', 'max:1000'],
        ]);

        $customer = $this->resolveCustomer($user);
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        $instructions = $validated['cooking_instructions'] ?? $validated['instructions'] ?? null;
        $customer->cart_cooking_instructions = $instructions;
        $customer->save();

        return response()->json([
            'status' => true,
            'message' => 'Cooking instructions updated',
            'data' => ['cart' => $this->buildCartPayloadForCustomer((int) $customer->customer_id)],
        ]);
    }

    public function applyPromo(Request $request)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $validated = $request->validate([
            'promo_code' => ['required', 'string', 'max:80'],
        ]);

        $customer = $this->resolveCustomer($user);
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        $code = strtoupper(trim($validated['promo_code']));
        $offer = DiscountOffer::active()
            ->currentlyValid()
            ->where('apply_to', DiscountOffer::APPLY_ALL)
            ->whereRaw('UPPER(title) = ?', [$code])
            ->first();

        if (!$offer) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or expired promo code',
            ], 422);
        }

        CustomerPromoResolver::syncCustomerCartPromo($customer, $offer);
        $customer->save();

        $cart = $this->buildCartPayloadForCustomer((int) $customer->customer_id);

        return response()->json([
            'status' => true,
            'message' => "Code '{$code}' applied! You saved ₹" . ($cart['promo_discount'] ?? '0.00'),
            'data' => ['cart' => $cart],
        ]);
    }

    public function removePromo(Request $request)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $customer = $this->resolveCustomer($user);
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        CustomerPromoResolver::clearCustomerCartPromo($customer);
        $customer->save();

        return response()->json([
            'status' => true,
            'message' => 'Promo code removed',
            'data' => ['cart' => $this->buildCartPayloadForCustomer((int) $customer->customer_id)],
        ]);
    }

    public function selectAddress(Request $request)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $validated = $request->validate([
            'customer_address_id' => ['required', 'integer'],
            'address_id' => ['nullable', 'integer'],
        ]);

        $addressId = (int) ($validated['customer_address_id'] ?? $validated['address_id']);

        $customer = Customers::with('addresses')->where('user_id', $user->user_id)->first();
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        $address = $customer->addresses->firstWhere('customer_address_id', $addressId);
        if (!$address) {
            return response()->json(['status' => false, 'message' => 'Address not found'], 404);
        }

        $customer->cart_selected_address_id = $addressId;
        $customer->save();

        return response()->json([
            'status' => true,
            'message' => 'Delivery address selected',
            'data' => [
                'delivery_address' => $this->transformAddress($address),
                'cart' => $this->buildCartPayloadForCustomer((int) $customer->customer_id),
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------
    private function minimumOrderQuantityForProduct($product): int
    {
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
        $customerQuery = Customers::query()->where('customer_id', $customerId);
        if (Schema::hasTable('customer_addresses')) {
            $customerQuery->with([
                'addresses' => fn ($q) => $q->orderByDesc('is_default')->orderByDesc('updated_at'),
                'defaultAddress',
            ]);
        }
        $customer = $customerQuery->first();
        if ($customer) {
            CustomerPromoResolver::sanitizeCustomerPromo($customer);
        }

        $activeVendorId = $this->resolveCartVendorId($customerId);

        $items = CartItem::with('product')
            ->where('customer_id', $customerId)
            ->get();

        return $this->buildCartResponse($items, $customer, $activeVendorId);
    }

    private function buildCartResponse($items, ?Customers $customer = null, ?int $activeVendorId = null): array
    {
        $cartItems = $items->map(fn (CartItem $item) => $this->formatCartItem($item))->values();

        $subtotal = $cartItems->sum(fn ($i) => (float) $i['line_total']);

        $lineOffers = CustomerPromoResolver::calculateAutomaticLineOfferDiscounts($items, $subtotal);
        $totalOfferDiscount = (float) $lineOffers['discount'];
        $appliedOffers = $lineOffers['applied'];

        $eligiblePromoSubtotal = max(0, round($subtotal - $totalOfferDiscount, 2));
        $promo = CustomerPromoResolver::resolveExplicitCartPromo($customer, $eligiblePromoSubtotal);
        $promoDiscount = (float) $promo['discount'];
        $promoCode = $promo['code'];

        $deliveryFee = $items->isEmpty() ? 0.0 : (float) config('customer-app.delivery_fee', 40);
        $taxAmount = $this->estimateCartTaxAmount($items);
        $totalAmount = max(0, $subtotal - $totalOfferDiscount - $promoDiscount + $deliveryFee + $taxAmount);

        $selectedAddress = null;
        if ($customer && Schema::hasTable('customer_addresses')) {
            if ($customer->cart_selected_address_id) {
                $selectedAddress = $customer->addresses
                    ?->firstWhere('customer_address_id', (int) $customer->cart_selected_address_id);
            }
            $selectedAddress = $selectedAddress ?: $customer->defaultAddress ?: $customer->addresses?->first();
        }

        return [
            'active_vendor_id' => $activeVendorId,
            'items' => $cartItems,
            'items_count' => $cartItems->count(),
            'other_vendor_groups' => [],
            'cooking_instructions' => $customer?->cart_cooking_instructions,
            'promo_code' => $promoCode,
            'promo_applied' => CustomerPromoResolver::customerHasExplicitCartPromo($customer) && $promoCode !== null,
            'has_promo_applied' => CustomerPromoResolver::customerHasExplicitCartPromo($customer) && $promoCode !== null,
            'promo_message' => $promoCode
                ? "Code '{$promoCode}' applied! You saved ₹" . number_format($promoDiscount, 2, '.', '')
                : null,
            'delivery_address' => $selectedAddress ? $this->transformAddress($selectedAddress) : null,
            'order_info' => [
                'subtotal' => number_format($subtotal, 2, '.', ''),
                'delivery_fee' => number_format($deliveryFee, 2, '.', ''),
                'gst_and_other_charges' => number_format($taxAmount, 2, '.', ''),
                'tax_amount' => number_format($taxAmount, 2, '.', ''),
                'offer_discount' => number_format($totalOfferDiscount, 2, '.', ''),
                'promo_discount' => number_format($promoDiscount, 2, '.', ''),
                'total_amount' => number_format($totalAmount, 2, '.', ''),
            ],
            'subtotal' => number_format($subtotal, 2, '.', ''),
            'delivery_fee' => number_format($deliveryFee, 2, '.', ''),
            'tax_amount' => number_format($taxAmount, 2, '.', ''),
            'offer_discount' => number_format($totalOfferDiscount, 2, '.', ''),
            'promo_discount' => number_format($promoDiscount, 2, '.', ''),
            'grand_total' => number_format($totalAmount, 2, '.', ''),
            'total_amount' => number_format($totalAmount, 2, '.', ''),
            'applied_offers' => array_values($appliedOffers),
        ];
    }

    private function resolveCartVendorId(int $customerId): ?int
    {
        if (Schema::hasColumn('customers', 'active_cart_vendor_id')) {
            $stored = Customers::where('customer_id', $customerId)->value('active_cart_vendor_id');
            if ($stored !== null) {
                return (int) $stored;
            }
        }

        $vendorId = CartItem::query()
            ->join('products', 'products.product_id', '=', 'cart_items.product_id')
            ->where('cart_items.customer_id', $customerId)
            ->whereNotNull('products.vendor_id')
            ->orderByDesc('cart_items.updated_at')
            ->orderByDesc('cart_items.cart_item_id')
            ->value('products.vendor_id');

        return $vendorId !== null ? (int) $vendorId : null;
    }

    private function productVendorId(Product $product): ?int
    {
        if (!Schema::hasColumn('products', 'vendor_id') || empty($product->vendor_id)) {
            return null;
        }

        return (int) $product->vendor_id;
    }

    private function customerHasCartItems(int $customerId): bool
    {
        return CartItem::where('customer_id', $customerId)->exists();
    }

    private function clearCartForCustomer(Customers $customer, bool $resetSession = true): void
    {
        CartItem::where('customer_id', $customer->customer_id)->delete();

        if (!$resetSession) {
            return;
        }

        if (Schema::hasColumn('customers', 'cart_cooking_instructions')) {
            $customer->cart_cooking_instructions = null;
        }
        CustomerPromoResolver::clearCustomerCartPromo($customer);
        if (Schema::hasColumn('customers', 'active_cart_vendor_id')) {
            $customer->active_cart_vendor_id = null;
        }

        $customer->save();
    }

    private function estimateCartTaxAmount($items): float
    {
        $totalTax = 0.0;

        foreach ($items as $item) {
            $product = $item->product;
            if (!$product) {
                continue;
            }

            $effectivePrice = (float) ($item->sale_price ?: $item->unit_price);
            $gstPercentage = (float) ($product->gst_percentage ?? 0);
            $gstType = $product->gst_calculation_type ?? Product::GST_EXCLUDED;

            if ($gstType === Product::GST_INCLUDED) {
                $baseUnitPrice = $gstPercentage > 0
                    ? round($effectivePrice / (1 + $gstPercentage / 100), 2)
                    : $effectivePrice;
                $gstPerUnit = round($effectivePrice - $baseUnitPrice, 2);
            } else {
                $gstPerUnit = $gstPercentage > 0
                    ? round($effectivePrice * $gstPercentage / 100, 2)
                    : 0.0;
            }

            $totalTax += round($gstPerUnit * (int) $item->quantity, 2);
        }

        return round($totalTax, 2);
    }

    private function transformAddress(CustomerAddress $address): array
    {
        return [
            'customer_address_id' => $address->customer_address_id,
            'contact_name' => $address->contact_name,
            'full_name' => $address->contact_name,
            'mobile' => $address->mobile,
            'mobile_number' => $address->mobile,
            'address_line' => $address->address_line,
            'landmark' => $address->landmark,
            'city' => $address->city,
            'state' => $address->state,
            'country' => $address->country,
            'pincode' => $address->pincode,
            'address_type' => $address->address_type,
            'delivery_type' => $address->address_type,
            'is_default' => (bool) $address->is_default,
            'formatted_address' => $address->formattedAddress(),
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
