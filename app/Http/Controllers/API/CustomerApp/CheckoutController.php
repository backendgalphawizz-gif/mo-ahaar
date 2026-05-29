<?php

namespace App\Http\Controllers\API\CustomerApp;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\CustomerApp\NotificationController;
use App\Models\CartItem;
use App\Models\CustomerAddress;
use App\Models\Customers;
use App\Models\Orders;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Users;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\BadRequestError;

class CheckoutController extends Controller
{
    private const CUSTOMER_ROLE_TYPE = Users::CUSTOMER_APP_ROLE_TYPE;
    private const ONLINE_PAYMENT_METHODS = ['online', 'upi', 'card', 'net_banking'];
    private const DEFAULT_PAYMENT_METHOD = 'online';

    // -----------------------------------------------------------------------
    // GET /api/customer-app/checkout/summary
    // Review cart summary with shipping details for confirmation
    // -----------------------------------------------------------------------
    public function summary(Request $request)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        if (!$user->canPlaceOrdersAsCustomer()) {
            return response()->json([
                'status' => false,
                'message' => 'Your account is pending admin approval. You cannot place orders until your GST details are verified and the account is activated.',
                'data' => [
                    'account_status' => $user->customerAccountApprovalLabel(),
                    'can_place_orders' => false,
                ],
            ], 403);
        }

        $customerQuery = Customers::query()->where('user_id', $user->user_id);
        if (\Illuminate\Support\Facades\Schema::hasTable('customer_addresses')) {
            $customerQuery->with([
                'addresses' => function ($query) {
                    $query->orderByDesc('is_default')->orderByDesc('updated_at')->orderByDesc('customer_address_id');
                },
                'defaultAddress',
            ]);
        }
        $customer = $customerQuery->first();
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        $items = $this->activeCartItems((int) $customer->customer_id);

        if ($items->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Your cart is empty'], 422);
        }

        if ($validationError = $this->validateCheckoutItems($items, $user)) {
            return $validationError;
        }

        $totals = $this->calculateCheckoutTotals($items, $customer);

        $selectedAddress = $this->resolveSelectedCartAddress($customer);

        return response()->json([
            'status'  => true,
            'message' => 'Checkout summary',
            'data'    => [
                'cart_items'       => $totals['cart_payload'],
                'items_count'      => count($totals['cart_payload']),
                'subtotal'         => number_format($totals['subtotal'], 2, '.', ''),
                'delivery_fee'     => number_format($totals['delivery_fee'], 2, '.', ''),
                'shipping_amount'  => number_format($totals['delivery_fee'], 2, '.', ''),
                'tax_amount'       => number_format($totals['tax_amount'], 2, '.', ''),
                'gst_and_other_charges' => number_format($totals['tax_amount'], 2, '.', ''),
                'promo_discount'   => number_format($totals['promo_discount'], 2, '.', ''),
                'offer_discount'   => number_format($totals['offer_discount'], 2, '.', ''),
                'total_amount'     => number_format($totals['total_amount'], 2, '.', ''),
                'cooking_instructions' => $customer->cart_cooking_instructions,
                'promo_code' => $customer->cart_promo_code,
                'shipping_details' => [
                    'full_name'       => $selectedAddress?->contact_name ?: $user->name,
                    'mobile'          => $selectedAddress?->mobile ?: $user->mobile,
                    'address'         => $selectedAddress?->formattedAddress() ?: $customer->customer_address,
                    'customer_address_id' => $selectedAddress?->customer_address_id,
                ],
                'delivery_address' => $selectedAddress ? $this->transformAddress($selectedAddress) : null,
                'shipping_addresses' => $customer->addresses->map(fn (CustomerAddress $address) => $this->transformAddress($address))->values(),
                'payment_methods'  => $this->availablePaymentMethods(),
                'order_info' => [
                    'subtotal' => number_format($totals['subtotal'], 2, '.', ''),
                    'delivery_fee' => number_format($totals['delivery_fee'], 2, '.', ''),
                    'gst_and_other_charges' => number_format($totals['tax_amount'], 2, '.', ''),
                    'promo_discount' => number_format($totals['promo_discount'], 2, '.', ''),
                    'total_amount' => number_format($totals['total_amount'], 2, '.', ''),
                ],
            ],
        ]);
    }

    public function paymentMethods(Request $request)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        return response()->json([
            'status' => true,
            'message' => 'Payment methods retrieved successfully',
            'data' => [
                'payment_methods' => $this->availablePaymentMethods(),
            ],
        ]);
    }

    // -----------------------------------------------------------------------
    // POST /api/customer-app/checkout/create-order
    // Create order in payment_pending status and return Razorpay order details
    // Body: {
    //   notes            (string, optional)
    // }
    // -----------------------------------------------------------------------
    public function createOrder(Request $request)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        if (!$user->canPlaceOrdersAsCustomer()) {
            return response()->json([
                'status' => false,
                'message' => 'Your account is pending admin approval. You cannot place orders until your GST details are verified and the account is activated.',
                'data' => [
                    'account_status' => $user->customerAccountApprovalLabel(),
                    'can_place_orders' => false,
                ],
            ], 403);
        }

        $validated = $request->validate([
            'notes' => 'nullable|string|max:500',
            'customer_address_id' => 'nullable|integer',
            'amount' => 'nullable|integer',
        ]);

        $customer = Customers::with(['addresses', 'defaultAddress'])->where('user_id', $user->user_id)->first();
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        // $shippingAddress = $this->resolveShippingAddress($customer, $validated['customer_address_id'] ?? null);
        // if (!$shippingAddress) {
        //     return response()->json(['status' => false, 'message' => 'Shipping address not found'], 404);
        // }

        $items = $this->activeCartItems((int) $customer->customer_id);

        if ($items->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Your cart is empty'], 422);
        }

        if ($validationError = $this->validateCheckoutItems($items, $user)) {
            return $validationError;
        }

        [$cartPayload, $subtotal, $totalTax] = $this->buildCartSummary($items);

        $paymentMethod = self::DEFAULT_PAYMENT_METHOD;
        $isOnlinePayment = true;

        // DB::beginTransaction();
        try {

            $total_amount     = $subtotal + $totalTax;

            // $vendorId = $items->first()->product->vendor_id ?? null;
            // $shippingAddressPayload = $shippingAddress->toArray();
            // $shippingAddressPayload['formatted_address'] = $shippingAddress->formattedAddress();
            // $shippingAddressJson = json_encode($shippingAddressPayload);

            // if ($shippingAddressJson === false) {
            //     throw new \RuntimeException('Unable to encode shipping address.');
            // }

            // $order = Orders::create([
            //     'customer_id'      => $customer->customer_id,
            //     'vendor_id'        => $vendorId,
            //     'order_number'     => $this->generateOrderNumber(),
            //     'subtotal'         => $subtotal,
            //     'tax_amount'       => $totalTax,
            //     'gst_amount'       => $totalTax,
            //     'shipping_amount'  => 0,
            //     'total_amount'     => $subtotal + $totalTax,
            //     'payment_method'   => $paymentMethod,
            //     'payment_status'   => 'pending',
            //     'order_status'     => $isOnlinePayment ? 'payment_pending' : 'pending',
            //     'shipping_address' => $shippingAddressJson,
            //     'notes'            => $validated['notes'] ?? null,
            // ]);

            // foreach ($items as $item) {
            //     $product   = $item->product;
            //     $gst       = $this->computeItemGst($item);

            //     $baseUnitPrice = $gst['base_unit_price'];
            //     $gstPerUnit    = $gst['gst_per_unit'];
            //     $lineGst       = round($gstPerUnit * $item->quantity, 2);
            //     $lineBase      = round($baseUnitPrice * $item->quantity, 2);
            //     $lineTotal     = round($lineBase + $lineGst, 2);

            //     $discount = $item->sale_price
            //         ? round(((float) $item->unit_price - (float) $item->sale_price) * $item->quantity, 2)
            //         : 0;

            //     OrderItem::create([
            //         'order_id'        => $order->order_id,
            //         'product_id'      => $item->product_id,
            //         'product_name'    => $product->product_name,
            //         'sku'             => $product->sku,
            //         'quantity'        => $item->quantity,
            //         'unit_price'      => $baseUnitPrice,
            //         'discount_amount' => $discount,
            //         'tax_amount'      => $lineGst,
            //         'gst_amount'      => $lineGst,
            //         'gst_percentage'  => $gst['gst_percentage'],
            //         'gst_calculation_type' => $gst['gst_type'],
            //         'effective_price' => round($baseUnitPrice + $gstPerUnit, 2),
            //         'line_total'      => $lineTotal,
            //         'item_status'     => $isOnlinePayment ? 'payment_pending' : 'pending',
            //     ]);
            // }

            if ($isOnlinePayment) {
                $razorpayData = $this->createRazorpayOrder($total_amount);
                $razorpayOrderId = $this->extractRazorpayOrderId($razorpayData);
                if (!$razorpayOrderId) {
                    throw new \RuntimeException('Unable to create Razorpay order id.');
                }

                // $order->razorpay_order_id = $razorpayOrderId;
                // $order->save();
            }

            // NotificationController::pushOrderUpdate(
            //     (int) $customer->customer_id,
            //     (int) $order->order_id,
            //     'Order Placed',
            //     'Your order ' . $order->order_number . ' has been placed successfully.',
            //     [
            //         'order_number' => $order->order_number,
            //         'order_status' => $order->order_status,
            //     ]
            // );

            // CartItem::where('customer_id', $customer->customer_id)->delete();

            // DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Order created. Complete payment to confirm.',
                'data'    => [
                    // 'order_id'        => $order->order_id,
                    // 'order_number'    => $order->order_number,
                    // 'subtotal'        => number_format((float) $order->subtotal, 2, '.', ''),
                    // 'tax_amount'      => number_format((float) $order->tax_amount, 2, '.', ''),
                    // 'gst_amount'      => number_format((float) ($order->gst_amount ?? $order->tax_amount), 2, '.', ''),
                    // 'total_amount'    => number_format((float) $order->total_amount, 2, '.', ''),
                    // 'payment_method'  => $order->payment_method,
                    // 'payment_status'  => $order->payment_status,
                    // 'order_status'    => $order->order_status,
                    // 'shipping_address' => $order->shipping_address,
                    // 'items_ordered'   => count($cartPayload),
                    // 'placed_at'       => $order->created_at->toDateTimeString(),
                    'razorpay'        => [
                        'razorpay_order_id' => $razorpayOrderId,
                        'razorpay_key_id' => config('services.razorpay.key_id'),
                        'currency' => 'INR',
                        'amount' => (int) round(((float) $total_amount) * 100),
                    ],
                ],
            ], 201);

        } catch (\Throwable $e) {
            // DB::rollBack();

            report($e);

            if ($e instanceof \RuntimeException) {
                return response()->json([
                    'status'  => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return response()->json([
                'status'  => false,
                'message' => 'Failed to create order. Please try again.',
            ], 500);
        }
    }

    // -----------------------------------------------------------------------
    // POST /api/customer-app/checkout/place-order
    // Place order for COD or online (single-step flow from app)
    // Body: {
    //   order_id             (int, required),
    //   razorpay_order_id    (string, required),
    //   razorpay_payment_id  (string, required),
    //   razorpay_signature   (string, required)
    // }
    // -----------------------------------------------------------------------
    public function placeOrder(Request $request)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        if (!$user->canPlaceOrdersAsCustomer()) {
            return response()->json([
                'status' => false,
                'message' => 'Your account is pending admin approval. You cannot place orders until your GST details are verified and the account is activated.',
                'data' => [
                    'account_status' => $user->customerAccountApprovalLabel(),
                    'can_place_orders' => false,
                ],
            ], 403);
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'string', Rule::in(['cod', 'cash_on_delivery', 'online', 'razorpay', 'upi', 'card', 'net_banking'])],
            'customer_address_id' => ['nullable', 'integer'],
            'address_id' => ['nullable', 'integer'],
            'shipping_address' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:500'],
            'razorpay_order_id' => ['nullable', 'string', 'max:255'],
            'razorpay_payment_id' => ['nullable', 'string', 'max:255'],
            'razorpay_signature' => ['nullable', 'string', 'max:255'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'payment_gateway' => ['nullable', 'string', 'max:50'],
            'payment_status' => ['nullable', Rule::in(['pending', 'paid', 'failed', 'cancelled', 'refunded'])],
            'payment_response' => ['nullable', 'array'],
        ]);

        $paymentMethod = $this->normalizePaymentMethod($validated['payment_method']);
        $isCod = in_array($paymentMethod, ['cod', 'cash_on_delivery'], true);
        $isOnline = !$isCod;

        $customerQuery = Customers::query()->where('user_id', $user->user_id);
        if (\Illuminate\Support\Facades\Schema::hasTable('customer_addresses')) {
            $customerQuery->with(['addresses', 'defaultAddress']);
        }
        $customer = $customerQuery->first();
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        $addressId = $validated['customer_address_id'] ?? $validated['address_id'] ?? $customer->cart_selected_address_id;
        $shippingAddressJson = $this->buildShippingAddressJson($customer, $user, $addressId, $validated['shipping_address'] ?? null);
        if ($shippingAddressJson === null) {
            return response()->json(['status' => false, 'message' => 'Shipping address not found'], 404);
        }

        $items = $this->activeCartItems((int) $customer->customer_id);
        if ($items->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Your cart is empty'], 422);
        }

        if ($validationError = $this->validateCheckoutItems($items, $user)) {
            return $validationError;
        }

        $totals = $this->calculateCheckoutTotals($items, $customer);
        $notes = $this->composeOrderNotes(
            trim((string) ($validated['notes'] ?? $customer->cart_cooking_instructions ?? '')),
            $validated
        );

        DB::beginTransaction();
        try {
            $onlinePaymentStatus = $validated['payment_status'] ?? 'paid';
            $onlineOrderStatus = $onlinePaymentStatus === 'paid' ? 'confirmed' : 'payment_pending';

            $order = $this->persistOrder(
                $customer,
                $items,
                $shippingAddressJson,
                $totals,
                $paymentMethod,
                $isCod ? 'pending' : $onlinePaymentStatus,
                $isCod ? 'pending' : $onlineOrderStatus,
                $notes,
                $customer->cart_promo_code,
                $customer->cart_discount_offer_id
            );

            if ($isOnline) {
                $order->razorpay_order_id = $validated['razorpay_order_id'] ?? ($validated['transaction_id'] ?? null);
                $order->razorpay_payment_id = $validated['razorpay_payment_id'] ?? ($validated['transaction_id'] ?? null);
                $order->razorpay_signature = $validated['razorpay_signature'] ?? null;
                $order->save();
                OrderItem::where('order_id', $order->order_id)->update([
                    'item_status' => $onlineOrderStatus === 'confirmed' ? 'confirmed' : 'payment_pending',
                ]);
            }

            $orderedProductIds = $items->pluck('product_id')->unique()->values()->all();
            CartItem::where('customer_id', $customer->customer_id)
                ->whereIn('product_id', $orderedProductIds)
                ->delete();
            if (\Illuminate\Support\Facades\Schema::hasColumn('customers', 'cart_promo_code')) {
                $customer->cart_promo_code = null;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('customers', 'cart_discount_offer_id')) {
                $customer->cart_discount_offer_id = null;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('customers', 'cart_cooking_instructions')) {
                $customer->cart_cooking_instructions = null;
            }
            $customer->save();

            DB::commit();

            NotificationController::pushOrderUpdate(
                (int) $customer->customer_id,
                (int) $order->order_id,
                'Order Placed',
                'Your order ' . $order->order_number . ' has been placed successfully.',
                [
                    'order_number' => $order->order_number,
                    'order_status' => $order->order_status,
                ]
            );

            return response()->json([
                'status' => true,
                'message' => 'Order placed successfully',
                'data' => [
                    'order_id' => $order->order_id,
                    'order_number' => $order->order_number,
                    'payment_method' => $order->payment_method,
                    'payment_status' => $order->payment_status,
                    'order_status' => $order->order_status,
                    'total_amount' => number_format((float) $order->total_amount, 2, '.', ''),
                    'promo_code' => $order->promo_code ?? null,
                    'promo_discount' => number_format((float) ($order->promo_discount ?? 0), 2, '.', ''),
                    'items_ordered' => $items->count(),
                    'track_order_url' => url('/api/customer-app/orders/' . $order->order_id . '/tracking'),
                ],
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'status' => false,
                'message' => 'Failed to place order. Please try again.',
            ], 500);
        }
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Compute GST breakdown for a single cart item.
     *
     * Returns:
     *   base_unit_price  – pre-GST price per unit
     *   gst_per_unit     – GST amount per unit
     *   gst_type         – 'included' | 'excluded'
     *   gst_percentage   – GST rate (e.g. 18.0)
     */
    private function computeItemGst(CartItem $item): array
    {
        $product        = $item->product;
        $effectivePrice = (float) ($item->sale_price ?: $item->unit_price);
        $gstPercentage  = $product ? (float) ($product->gst_percentage ?? 0) : 0.0;
        $gstType        = $product
            ? ($product->gst_calculation_type ?? Product::GST_EXCLUDED)
            : Product::GST_EXCLUDED;

        if ($gstType === Product::GST_INCLUDED) {
            // Price already contains GST – extract the base and the GST component
            $baseUnitPrice = $gstPercentage > 0
                ? round($effectivePrice / (1 + $gstPercentage / 100), 2)
                : $effectivePrice;
            $gstPerUnit    = round($effectivePrice - $baseUnitPrice, 2);
        } else {
            // Price is before GST – add GST on top
            $baseUnitPrice = $effectivePrice;
            $gstPerUnit    = $gstPercentage > 0
                ? round($effectivePrice * $gstPercentage / 100, 2)
                : 0.0;
        }

        return [
            'base_unit_price' => $baseUnitPrice,
            'gst_per_unit'    => $gstPerUnit,
            'gst_type'        => $gstType,
            'gst_percentage'  => $gstPercentage,
        ];
    }

    private function buildCartSummary($items): array
    {
        $payload   = [];
        $subtotal  = 0.0;
        $totalTax  = 0.0;

        foreach ($items as $item) {
            $product = $item->product;
            $gst     = $this->computeItemGst($item);

            $baseUnitPrice = $gst['base_unit_price'];
            $gstPerUnit    = $gst['gst_per_unit'];
            $lineBase      = round($baseUnitPrice * $item->quantity, 2);
            $lineGst       = round($gstPerUnit * $item->quantity, 2);
            $lineTotal     = round($lineBase + $lineGst, 2);

            $subtotal  += $lineBase;
            $totalTax  += $lineGst;

            $payload[] = [
                'cart_item_id'         => $item->cart_item_id,
                'product_id'           => $item->product_id,
                'product_name'         => $product ? $product->product_name : null,
                'product_image_url'    => ($product && !empty($product->product_image))
                    ? url('public/uploads/products/' . $product->product_image)
                    : null,
                'quantity'             => $item->quantity,
                'unit_price'           => number_format((float) $item->unit_price, 2, '.', ''),
                'sale_price'           => $item->sale_price ? number_format((float) $item->sale_price, 2, '.', '') : null,
                'base_price'           => number_format($baseUnitPrice, 2, '.', ''),
                'gst_calculation_type' => $gst['gst_type'],
                'gst_percentage'       => number_format($gst['gst_percentage'], 2, '.', ''),
                'gst_amount'           => number_format($lineGst, 2, '.', ''),
                'line_total'           => number_format($lineTotal, 2, '.', ''),
                'effective_price'      => number_format($baseUnitPrice + $gstPerUnit, 2, '.', ''),
            ];
        }

        return [$payload, round($subtotal, 2), round($totalTax, 2)];
    }

    private function validateCheckoutItems($items, Users $user)
    {
        $vendorIds = [];

        foreach ($items as $item) {
            $product = $item->product;

            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'One or more products in your cart are no longer available.',
                    'data' => [
                        'reason' => 'missing_product',
                        'product_id' => $item->product_id,
                        'cart_item_id' => $item->cart_item_id,
                    ],
                ], 422);
            }

            $visibleProduct = Product::query()
                ->visibleToCustomerUser($user)
                ->where('product_id', $product->product_id)
                ->whereIn('status', [1, '1'])
                ->whereIn('is_active_status', [1, '1'])
                ->exists();

            if (!$visibleProduct) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product "' . $product->product_name . '" is currently unavailable.',
                    'data' => [
                        'reason' => 'unavailable_product',
                        'product_id' => $product->product_id,
                        'cart_item_id' => $item->cart_item_id,
                    ],
                ], 422);
            }

            if ($product->vendor_id !== null && $product->vendor_id !== '') {
                $vendorIds[] = (string) $product->vendor_id;
            }
        }

        $vendorIds = array_values(array_unique($vendorIds));

        if (count($vendorIds) > 1) {
            return response()->json([
                'status' => false,
                'message' => 'Your cart contains products from multiple vendors. Please place separate orders for each vendor.',
                'data' => [
                    'reason' => 'mixed_vendor_cart',
                    'vendor_ids' => $vendorIds,
                ],
            ], 422);
        }

        return null;
    }

    private function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(Str::random(4)) . '-' . now()->format('ymdHis');
    }

    private function createRazorpayOrder($amount)
    {
        $keyId = config('services.razorpay.key_id');
        $keySecret = config('services.razorpay.key_secret');

        if (empty($keyId) || empty($keySecret)
            || $keyId === 'rzp_test_your_key_id'
            || $keySecret === 'your_key_secret') {
            throw new \RuntimeException('Razorpay credentials are not configured');
        }

        $api = new Api($keyId, $keySecret);
        $orderNumber = $this->generateOrderNumber();
        try {
            return $api->order->create([
                // 'receipt' => $order->order_number,
                'receipt' => $orderNumber,
                'amount' => (int) round(((float) $amount) * 100),
                'currency' => 'INR',
                'payment_capture' => 1,
            ]);
        } catch (BadRequestError $e) {
            $message = (string) $e->getMessage();

            if (stripos($message, 'Authentication failed') !== false) {
                throw new \RuntimeException('Razorpay authentication failed. Please verify the test key ID and key secret.');
            }

            if (stripos($message, 'Amount exceeds maximum amount allowed') !== false) {
                throw new \RuntimeException('Order amount exceeds Razorpay limit. Please reduce quantity or split into multiple orders.');
            }

            if (stripos($message, 'amount') !== false) {
                throw new \RuntimeException('Invalid payment amount. Please review cart totals and try again.');
            }

            throw $e;
        }
    }

    private function extractRazorpayOrderId($razorpayData): ?string
    {
        if (is_array($razorpayData) && !empty($razorpayData['id'])) {
            return (string) $razorpayData['id'];
        }

        if (is_object($razorpayData)) {
            if (isset($razorpayData->id) && !empty($razorpayData->id)) {
                return (string) $razorpayData->id;
            }

            if ($razorpayData instanceof \ArrayAccess && isset($razorpayData['id']) && !empty($razorpayData['id'])) {
                return (string) $razorpayData['id'];
            }
        }

        return null;
    }

    private function isAuthorizedCustomer(?Users $user): bool
    {
        return $user && (int) $user->role_type === self::CUSTOMER_ROLE_TYPE;
    }

    private function resolveShippingAddress(Customers $customer, ?int $addressId): ?CustomerAddress
    {
        if ($addressId !== null) {
            return $customer->addresses->firstWhere('customer_address_id', $addressId);
        }

        return $customer->defaultAddress ?: $customer->addresses->first();
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

    private function availablePaymentMethods(): array
    {
        return [
            [
                'id' => 'online',
                'key' => 'online',
                'label' => 'ONLINE PAYMENT',
                'name' => 'Razor Pay',
                'provider' => 'Razorpay',
                'type' => 'online',
            ],
            [
                'id' => 'cod',
                'key' => 'cod',
                'label' => 'PAY VIA CASH',
                'name' => 'Cash On Delivery',
                'provider' => 'COD',
                'type' => 'cod',
            ],
        ];
    }

    private function normalizePaymentMethod(string $method): string
    {
        $method = strtolower(trim($method));

        return match ($method) {
            'cash_on_delivery', 'cash' => 'cod',
            'razorpay', 'upi', 'card', 'net_banking' => 'online',
            default => $method,
        };
    }

    private function resolveSelectedCartAddress(Customers $customer): ?CustomerAddress
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('customer_addresses')) {
            return null;
        }

        if ($customer->cart_selected_address_id) {
            $selected = $customer->addresses
                ?->firstWhere('customer_address_id', (int) $customer->cart_selected_address_id);
            if ($selected) {
                return $selected;
            }
        }

        return $customer->defaultAddress ?: $customer->addresses->first();
    }

    private function buildShippingAddressJson(
        Customers $customer,
        Users $user,
        ?int $addressId,
        ?string $shippingAddressText
    ): ?string {
        if ($addressId && \Illuminate\Support\Facades\Schema::hasTable('customer_addresses') && $customer->relationLoaded('addresses')) {
            $address = $customer->addresses->firstWhere('customer_address_id', $addressId);
            if (!$address) {
                return null;
            }
            $payload = $address->toArray();
            $payload['formatted_address'] = $address->formattedAddress();

            return json_encode($payload) ?: null;
        }

        if (is_string($shippingAddressText) && trim($shippingAddressText) !== '') {
            return json_encode([
                'contact_name' => $user->name,
                'mobile' => $user->mobile,
                'address_line' => trim($shippingAddressText),
                'formatted_address' => trim($shippingAddressText),
            ]) ?: null;
        }

        $fallback = $this->resolveSelectedCartAddress($customer);
        if (!$fallback) {
            return null;
        }

        $payload = $fallback->toArray();
        $payload['formatted_address'] = $fallback->formattedAddress();

        return json_encode($payload) ?: null;
    }

    /**
     * @return array<string, mixed>
     */
    private function calculateCheckoutTotals($items, Customers $customer): array
    {
        [$cartPayload, $subtotal, $totalTax] = $this->buildCartSummary($items);

        $lineSubtotal = $cartPayload ? array_sum(array_map(
            fn ($row) => (float) $row['line_total'],
            $cartPayload
        )) : 0.0;

        $offerDiscount = max(0, $lineSubtotal - $subtotal);
        $promoDiscount = 0.0;

        if ($customer->cart_discount_offer_id && \Illuminate\Support\Facades\Schema::hasTable('discount_offers')) {
            $promoOffer = \App\Models\DiscountOffer::active()
                ->currentlyValid()
                ->find($customer->cart_discount_offer_id);
            if ($promoOffer && $promoOffer->cartAmountConditionMet($subtotal)) {
                $base = max(0, $subtotal);
                if ($promoOffer->discount_type === \App\Models\DiscountOffer::TYPE_PERCENTAGE) {
                    $promoDiscount = round($base * ((float) $promoOffer->discount_value / 100), 2);
                } else {
                    $promoDiscount = round(min((float) $promoOffer->discount_value, $base), 2);
                }
            }
        }

        $deliveryFee = $items->isEmpty() ? 0.0 : (float) config('customer-app.delivery_fee', 40);
        $totalAmount = max(0, $subtotal - $promoDiscount + $deliveryFee + $totalTax);

        return [
            'cart_payload' => $cartPayload,
            'subtotal' => round($subtotal, 2),
            'offer_discount' => round($offerDiscount, 2),
            'promo_discount' => round($promoDiscount, 2),
            'delivery_fee' => round($deliveryFee, 2),
            'tax_amount' => round($totalTax, 2),
            'total_amount' => round($totalAmount, 2),
        ];
    }

    private function persistOrder(
        Customers $customer,
        $items,
        string $shippingAddressJson,
        array $totals,
        string $paymentMethod,
        string $paymentStatus,
        string $orderStatus,
        string $notes,
        ?string $promoCode = null,
        ?int $discountOfferId = null
    ): Orders {
        $vendorId = $items->first()->product->vendor_id ?? null;

        $orderData = [
            'customer_id' => $customer->customer_id,
            'vendor_id' => $vendorId,
            'order_number' => $this->generateOrderNumber(),
            'subtotal' => $totals['subtotal'],
            'tax_amount' => $totals['tax_amount'],
            'shipping_amount' => $totals['delivery_fee'],
            'total_amount' => $totals['total_amount'],
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'order_status' => $orderStatus,
            'shipping_address' => $shippingAddressJson,
            'notes' => $notes !== '' ? $notes : null,
        ];

        if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'gst_amount')) {
            $orderData['gst_amount'] = $totals['tax_amount'];
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'promo_code')) {
            $orderData['promo_code'] = $promoCode ? strtoupper(trim($promoCode)) : null;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'promo_discount')) {
            $orderData['promo_discount'] = $totals['promo_discount'] ?? 0;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'discount_offer_id')) {
            $orderData['discount_offer_id'] = $discountOfferId;
        }
        if (\Illuminate\Support\Facades\Schema::hasColumn('orders', 'offer_discount')) {
            $orderData['offer_discount'] = $totals['offer_discount'] ?? 0;
        }

        $order = Orders::create($orderData);

        foreach ($items as $item) {
            $product = $item->product;
            $gst = $this->computeItemGst($item);

            $baseUnitPrice = $gst['base_unit_price'];
            $gstPerUnit = $gst['gst_per_unit'];
            $lineGst = round($gstPerUnit * $item->quantity, 2);
            $lineBase = round($baseUnitPrice * $item->quantity, 2);
            $lineTotal = round($lineBase + $lineGst, 2);

            $discount = $item->sale_price
                ? round(((float) $item->unit_price - (float) $item->sale_price) * $item->quantity, 2)
                : 0;

            $orderItemData = [
                'order_id' => $order->order_id,
                'product_id' => $item->product_id,
                'product_name' => $product->product_name,
                'sku' => $product->sku,
                'quantity' => $item->quantity,
                'unit_price' => $baseUnitPrice,
                'discount_amount' => $discount,
                'tax_amount' => $lineGst,
                'line_total' => $lineTotal,
                'item_status' => $orderStatus === 'confirmed' ? 'confirmed' : 'pending',
            ];

            if (\Illuminate\Support\Facades\Schema::hasColumn('order_items', 'gst_amount')) {
                $orderItemData['gst_amount'] = $lineGst;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('order_items', 'gst_percentage')) {
                $orderItemData['gst_percentage'] = $gst['gst_percentage'];
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('order_items', 'gst_calculation_type')) {
                $orderItemData['gst_calculation_type'] = $gst['gst_type'];
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('order_items', 'effective_price')) {
                $orderItemData['effective_price'] = round($baseUnitPrice + $gstPerUnit, 2);
            }

            OrderItem::create($orderItemData);
        }

        return $order;
    }

    private function activeCartItems(int $customerId)
    {
        return CartItem::with('product')
            ->where('customer_id', $customerId)
            ->get();
    }

    /**
     * Store app-side payment/transaction context in order notes safely.
     *
     * @param  array<string,mixed>  $validated
     */
    private function composeOrderNotes(string $baseNotes, array $validated): string
    {
        $paymentMeta = array_filter([
            'payment_gateway' => $validated['payment_gateway'] ?? null,
            'transaction_id' => $validated['transaction_id'] ?? null,
            'razorpay_order_id' => $validated['razorpay_order_id'] ?? null,
            'razorpay_payment_id' => $validated['razorpay_payment_id'] ?? null,
            'razorpay_signature' => $validated['razorpay_signature'] ?? null,
            'payment_status' => $validated['payment_status'] ?? null,
            'payment_response' => $validated['payment_response'] ?? null,
        ], static fn ($v) => $v !== null && $v !== '');

        if (empty($paymentMeta)) {
            return $baseNotes;
        }

        $json = json_encode($paymentMeta, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return $baseNotes;
        }

        $paymentLine = 'Payment Meta: ' . $json;
        if ($baseNotes === '') {
            return $paymentLine;
        }

        return $baseNotes . "\n" . $paymentLine;
    }
}
