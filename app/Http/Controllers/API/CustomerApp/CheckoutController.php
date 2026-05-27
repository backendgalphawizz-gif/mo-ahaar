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
use Razorpay\Api\Api;
use Razorpay\Api\Errors\BadRequestError;
use Razorpay\Api\Errors\SignatureVerificationError;

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

        $customer = Customers::with([
            'addresses' => function ($query) {
                $query->orderByDesc('is_default')->orderByDesc('updated_at')->orderByDesc('customer_address_id');
            },
            'defaultAddress',
        ])->where('user_id', $user->user_id)->first();
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        $items = CartItem::with('product')
            ->where('customer_id', $customer->customer_id)
            ->get();

        if ($items->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Your cart is empty'], 422);
        }

        if ($validationError = $this->validateCheckoutItems($items, $user)) {
            return $validationError;
        }

        [$cartPayload, $subtotal, $totalTax] = $this->buildCartSummary($items);

        $defaultAddress = $customer->defaultAddress ?: $customer->addresses->first();

        return response()->json([
            'status'  => true,
            'message' => 'Checkout summary',
            'data'    => [
                'cart_items'       => $cartPayload,
                'items_count'      => count($cartPayload),
                'subtotal'         => number_format($subtotal, 2, '.', ''),
                'shipping_amount'  => '0.00',
                'tax_amount'       => number_format($totalTax, 2, '.', ''),
                'total_amount'     => number_format($subtotal + $totalTax, 2, '.', ''),
                'shipping_details' => [
                    'full_name'       => $user->name,
                    'mobile'          => $user->mobile,
                    'address'         => $defaultAddress?->formattedAddress() ?: $customer->customer_address,
                    'customer_address_id' => $defaultAddress?->customer_address_id,
                ],
                'shipping_addresses' => $customer->addresses->map(fn (CustomerAddress $address) => $this->transformAddress($address))->values(),
                'payment_methods'  => [
                    ['key' => 'razorpay', 'label' => 'Razorpay'],
                ],
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

        $items = CartItem::with('product')
            ->where('customer_id', $customer->customer_id)
            ->get();

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
    // Finalize order after Razorpay payment response
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
            // 'order_id' => ['required', 'integer'],
            'payment_method' => ['nullable', 'string', 'in:online,upi,card,net_banking,cod,cash,razorpay'],
            'customer_address_id' => ['nullable', 'integer'],
            'razorpay_order_id' => ['required_if:payment_method,online,upi,card,net_banking,razorpay', 'nullable', 'string'],
            'razorpay_payment_id' => ['required_if:payment_method,online,upi,card,net_banking,razorpay', 'nullable', 'string'],
            'razorpay_signature' => ['required_if:payment_method,online,upi,card,net_banking,razorpay', 'nullable', 'string'],
        ]);

        // $customer = Customers::where('user_id', $user->user_id)->first();
        // if (!$customer) {
        //     return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        // }

        $customer = Customers::with(['addresses', 'defaultAddress'])->where('user_id', $user->user_id)->first();
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        $shippingAddress = $this->resolveShippingAddress($customer, $validated['customer_address_id'] ?? null);
        if (!$shippingAddress) {
            return response()->json(['status' => false, 'message' => 'Shipping address not found'], 404);
        }

        $items = CartItem::with('product')
            ->where('customer_id', $customer->customer_id)
            ->get();

        if ($items->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Your cart is empty'], 422);
        }

        if ($validationError = $this->validateCheckoutItems($items, $user)) {
            return $validationError;
        }

        [$cartPayload, $subtotal, $totalTax] = $this->buildCartSummary($items);

        $paymentMethod = self::DEFAULT_PAYMENT_METHOD;
        $isOnlinePayment = true;

        // $order = Orders::where('order_id', $validated['order_id'])
        //     ->where('customer_id', $customer->customer_id)
        //     ->first();

        // if (!$order) {
        //     return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        // }

        // if ($order->payment_status === 'paid') {
        //     return response()->json([
        //         'status' => true,
        //         'message' => 'Order already placed.',
        //         'data' => [
        //             'order_id' => $order->order_id,
        //             'order_number' => $order->order_number,
        //             'payment_status' => $order->payment_status,
        //             'order_status' => $order->order_status,
        //         ],
        //     ]);
        // }

        $keyId = config('services.razorpay.key_id');
        $keySecret = config('services.razorpay.key_secret');

        if (empty($keyId) || empty($keySecret)) {
            return response()->json([
                'status' => false,
                'message' => 'Razorpay credentials are not configured',
            ], 500);
        }

        DB::beginTransaction();
        try {
            $api = new Api($keyId, $keySecret);
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $validated['razorpay_order_id'],
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature' => $validated['razorpay_signature'],
            ]);

            $vendorId = $items->first()->product->vendor_id ?? null;
            $shippingAddressPayload = $shippingAddress->toArray();
            $shippingAddressPayload['formatted_address'] = $shippingAddress->formattedAddress();
            $shippingAddressJson = json_encode($shippingAddressPayload);

            if ($shippingAddressJson === false) {
                throw new \RuntimeException('Unable to encode shipping address.');
            }

            $order = Orders::create([
                'customer_id'      => $customer->customer_id,
                'vendor_id'        => $vendorId,
                'order_number'     => $this->generateOrderNumber(),
                'subtotal'         => $subtotal,
                'tax_amount'       => $totalTax,
                'gst_amount'       => $totalTax,
                'shipping_amount'  => 0,
                'total_amount'     => $subtotal + $totalTax,
                'payment_method'   => $paymentMethod,
                'payment_status'   => 'pending',
                'order_status'     => $isOnlinePayment ? 'payment_pending' : 'pending',
                'shipping_address' => $shippingAddressJson,
                'notes'            => $validated['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $product   = $item->product;
                $gst       = $this->computeItemGst($item);

                $baseUnitPrice = $gst['base_unit_price'];
                $gstPerUnit    = $gst['gst_per_unit'];
                $lineGst       = round($gstPerUnit * $item->quantity, 2);
                $lineBase      = round($baseUnitPrice * $item->quantity, 2);
                $lineTotal     = round($lineBase + $lineGst, 2);

                $discount = $item->sale_price
                    ? round(((float) $item->unit_price - (float) $item->sale_price) * $item->quantity, 2)
                    : 0;

                OrderItem::create([
                    'order_id'        => $order->order_id,
                    'product_id'      => $item->product_id,
                    'product_name'    => $product->product_name,
                    'sku'             => $product->sku,
                    'quantity'        => $item->quantity,
                    'unit_price'      => $baseUnitPrice,
                    'discount_amount' => $discount,
                    'tax_amount'      => $lineGst,
                    'gst_amount'      => $lineGst,
                    'gst_percentage'  => $gst['gst_percentage'],
                    'gst_calculation_type' => $gst['gst_type'],
                    'effective_price' => round($baseUnitPrice + $gstPerUnit, 2),
                    'line_total'      => $lineTotal,
                    'item_status'     => $isOnlinePayment ? 'payment_pending' : 'pending',
                ]);
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

            CartItem::where('customer_id', $customer->customer_id)->delete();
            
            DB::commit();

            DB::transaction(function () use ($order, $validated, $customer) {
                $order->razorpay_order_id = $validated['razorpay_order_id'];
                $order->razorpay_payment_id = $validated['razorpay_payment_id'];
                $order->razorpay_signature = $validated['razorpay_signature'];
                $order->payment_status = 'paid';
                $order->order_status = 'confirmed';
                $order->save();

                OrderItem::where('order_id', $order->order_id)->update([
                    'item_status' => 'confirmed',
                ]);

                $orderItems = OrderItem::where('order_id', $order->order_id)->get();
                $orderedProductIds = [];

                foreach ($orderItems as $orderItem) {
                    $orderedProductIds[] = $orderItem->product_id;
                    $product = Product::where('product_id', $orderItem->product_id)->first();
                    if ($product && $product->stock !== null) {
                        $product->decrement('stock', $orderItem->quantity);
                    }
                }

                if (!empty($orderedProductIds)) {
                    CartItem::where('customer_id', $customer->customer_id)
                        ->whereIn('product_id', array_unique($orderedProductIds))
                        ->delete();
                }

                // NotificationController::pushOrderUpdate(
                //     (int) $customer->customer_id,
                //     (int) $order->order_id,
                //     'Order Confirmed',
                //     'Payment received for order ' . $order->order_number . '. Your order is now confirmed.',
                //     [
                //         'order_number' => $order->order_number,
                //         'payment_status' => $order->payment_status,
                //         'order_status' => $order->order_status,
                //     ]
                // );
            });

            
            
            return response()->json([
                'status' => true,
                'message' => 'Order placed successfully',
                'data' => [
                    'order_id' => $order->order_id,
                    'order_number' => $order->order_number,
                    'payment_status' => $order->payment_status,
                    'order_status' => $order->order_status,
                    'razorpay_payment_id' => $order->razorpay_payment_id,
                ],
            ]);
        } catch (SignatureVerificationError $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid Razorpay signature',
            ], 422);
        } catch (\Throwable $e) {
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

            if ($product->stock !== null && $product->stock < $item->quantity) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Insufficient stock for "' . $product->product_name . '".',
                    'data' => [
                        'reason' => 'insufficient_stock',
                        'product_id' => $product->product_id,
                        'cart_item_id' => $item->cart_item_id,
                        'requested_quantity' => (int) $item->quantity,
                        'available_stock' => (int) $product->stock,
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
            'mobile' => $address->mobile,
            'address_line' => $address->address_line,
            'landmark' => $address->landmark,
            'city' => $address->city,
            'state' => $address->state,
            'country' => $address->country,
            'pincode' => $address->pincode,
            'address_type' => $address->address_type,
            'is_default' => (bool) $address->is_default,
            'formatted_address' => $address->formattedAddress(),
        ];
    }
}
