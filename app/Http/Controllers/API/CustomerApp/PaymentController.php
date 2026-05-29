<?php

namespace App\Http\Controllers\API\CustomerApp;

use App\Http\Controllers\API\Concerns\RespondsWithAccountRestrictions;
use App\Http\Controllers\Controller;
use App\Http\Controllers\API\CustomerApp\NotificationController;
use App\Models\CartItem;
use App\Models\Customers;
use App\Models\OrderItem;
use App\Models\Orders;
use App\Models\Product;
use App\Models\Users;
use App\Services\OrderDispatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class PaymentController extends Controller
{
    use RespondsWithAccountRestrictions;

    private const CUSTOMER_ROLE_TYPE = 2;

    // -----------------------------------------------------------------------
    // POST /api/customer-app/payment/razorpay/create-order
    // Body: { order_id }
    // Creates Razorpay order for an existing pending order
    // -----------------------------------------------------------------------
    public function createRazorpayOrder(Request $request)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        if ($denied = $this->denyCustomerOrder($user)) {
            return $denied;
        }

        $request->validate([
            'order_id' => 'required|integer',
        ]);

        $customer = Customers::where('user_id', $user->user_id)->first();
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        $order = Orders::where('order_id', $request->order_id)
            ->where('customer_id', $customer->customer_id)
            ->first();

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        $keyId = config('services.razorpay.key_id');
        $keySecret = config('services.razorpay.key_secret');

        if (!empty($order->razorpay_order_id)) {
            return response()->json([
                'status' => true,
                'message' => 'Razorpay order already exists',
                'data' => [
                    'order_id' => $order->order_id,
                    'order_number' => $order->order_number,
                    'amount' => number_format((float) $order->total_amount, 2, '.', ''),
                    'currency' => 'INR',
                    'razorpay_order_id' => $order->razorpay_order_id,
                    'razorpay_key_id' => $keyId,
                ],
            ]);
        }

        if ($order->payment_method !== 'online' && $order->payment_method !== 'upi' && $order->payment_method !== 'card' && $order->payment_method !== 'net_banking') {
            return response()->json([
                'status' => false,
                'message' => 'This order is not marked for online payment',
            ], 422);
        }

        if (empty($keyId) || empty($keySecret)) {
            return response()->json([
                'status' => false,
                'message' => 'Razorpay credentials are not configured',
            ], 500);
        }

        try {
            $api = new Api($keyId, $keySecret);

            // Create Razorpay order in paise
            $razorpayOrder = $api->order->create([
                'receipt'         => $order->order_number,
                'amount'          => (int) round(((float) $order->total_amount) * 100),
                'currency'        => 'INR',
                'payment_capture' => 1,
            ]);

            $order->razorpay_order_id = $razorpayOrder['id'];
            $order->payment_status    = 'pending';
            $order->save();

            return response()->json([
                'status'  => true,
                'message' => 'Razorpay order created successfully',
                'data'    => [
                    'order_id'           => $order->order_id,
                    'order_number'       => $order->order_number,
                    'amount'             => number_format((float) $order->total_amount, 2, '.', ''),
                    'currency'           => 'INR',
                    'razorpay_order_id'  => $razorpayOrder['id'],
                    'razorpay_key_id'    => $keyId,
                ],
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to create Razorpay order',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // -----------------------------------------------------------------------
    // POST /api/customer-app/payment/razorpay/verify
    // Body: { order_id, razorpay_order_id, razorpay_payment_id, razorpay_signature }
    // Verifies Razorpay signature after successful payment on app
    // -----------------------------------------------------------------------
    public function verifyRazorpayPayment(Request $request)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        if ($denied = $this->denyCustomerOrder($user)) {
            return $denied;
        }

        $request->validate([
            'order_id'             => 'required|integer',
            'razorpay_order_id'    => 'required|string',
            'razorpay_payment_id'  => 'required|string',
            'razorpay_signature'   => 'required|string',
        ]);

        $customer = Customers::where('user_id', $user->user_id)->first();
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        $order = Orders::where('order_id', $request->order_id)
            ->where('customer_id', $customer->customer_id)
            ->first();
        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        $keyId     = config('services.razorpay.key_id');
        $keySecret = config('services.razorpay.key_secret');

        if (empty($keyId) || empty($keySecret)) {
            return response()->json([
                'status' => false,
                'message' => 'Razorpay credentials are not configured',
            ], 500);
        }

        try {
            $api = new Api($keyId, $keySecret);
            $attributes = [
                'razorpay_order_id'   => $request->razorpay_order_id,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature'  => $request->razorpay_signature,
            ];
            $api->utility->verifyPaymentSignature($attributes);

            DB::transaction(function () use ($order, $customer, $request) {
                $order->razorpay_order_id = $request->razorpay_order_id;
                $order->razorpay_payment_id = $request->razorpay_payment_id;
                $order->razorpay_signature = $request->razorpay_signature;
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
                }

                if (!empty($orderedProductIds)) {
                    CartItem::where('customer_id', $customer->customer_id)
                        ->whereIn('product_id', array_unique($orderedProductIds))
                        ->delete();
                }

                NotificationController::pushOrderUpdate(
                    (int) $customer->customer_id,
                    (int) $order->order_id,
                    'Order Confirmed',
                    'Payment received for order ' . $order->order_number . '. Your order is now confirmed.',
                    [
                        'order_number' => $order->order_number,
                        'payment_status' => $order->payment_status,
                        'order_status' => $order->order_status,
                    ]
                );
            });

            app(OrderDispatchService::class)->dispatchAfterOrderPlaced($order->fresh(['vendor', 'customer.user']));

            return response()->json([
                'status'  => true,
                'message' => 'Payment verified successfully',
                'data'    => [
                    'order_id'            => $order->order_id,
                    'order_number'        => $order->order_number,
                    'payment_status'      => $order->payment_status,
                    'order_status'        => $order->order_status,
                    'razorpay_payment_id' => $order->razorpay_payment_id,
                ],
            ]);
        } catch (SignatureVerificationError $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid Razorpay signature',
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to verify payment',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // -----------------------------------------------------------------------
    // POST /api/customer-app/payment/update-status
    // Body: {
    //   order_id            (int, required),
    //   razorpay_order_id   (string, required),
    //   razorpay_signature  (string, required),
    //   payment_status      (string, required),
    //   order_status        (string, required)
    // }
    // Updates payment/order status by order_id for the authenticated customer
    // -----------------------------------------------------------------------
    public function updatePaymentStatus(Request $request)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $validated = $request->validate([
            'order_id' => ['required', 'integer'],
            'razorpay_order_id' => ['required', 'string', 'max:255'],
            'razorpay_signature' => ['required', 'string', 'max:255'],
            'payment_status' => ['required', Rule::in(['pending', 'paid', 'failed', 'cancelled', 'refunded'])],
            'order_status' => ['required', Rule::in(['pending', 'payment_pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'returned'])],
        ]);

        $customer = Customers::where('user_id', $user->user_id)->first();
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        $order = Orders::where('order_id', $validated['order_id'])
            ->where('customer_id', $customer->customer_id)
            ->first();

        if (!$order) {
            return response()->json(['status' => false, 'message' => 'Order not found'], 404);
        }

        if (!empty($order->razorpay_order_id) && $order->razorpay_order_id !== $validated['razorpay_order_id']) {
            return response()->json([
                'status' => false,
                'message' => 'Razorpay order id does not match this order.',
            ], 422);
        }

        $order->razorpay_order_id = $validated['razorpay_order_id'];
        $order->razorpay_signature = $validated['razorpay_signature'];
        $order->payment_status = $validated['payment_status'];
        $order->order_status = $validated['order_status'];
        $order->save();

        return response()->json([
            'status' => true,
            'message' => 'Payment status updated successfully.',
            'data' => [
                'order_id' => $order->order_id,
                'order_number' => $order->order_number,
                'razorpay_order_id' => $order->razorpay_order_id,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status,
            ],
        ]);
    }

    private function isAuthorizedCustomer(?Users $user): bool
    {
        return $user && (int) $user->role_type === self::CUSTOMER_ROLE_TYPE;
    }
}
