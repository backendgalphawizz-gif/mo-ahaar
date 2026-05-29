<?php

namespace App\Http\Controllers\API\CustomerApp;

use App\Http\Controllers\Controller;
use App\Models\Customers;
use App\Models\DiscountOffer;
use App\Models\Users;
use App\Services\CustomerPromoResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PromoController extends Controller
{
    private const CUSTOMER_ROLE_TYPE = Users::CUSTOMER_APP_ROLE_TYPE;

    /**
     * GET /api/customer-app/promo/list?order_amount=999
     */
    public function list(Request $request)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $validated = $request->validate([
            'order_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $orderAmount = round((float) ($validated['order_amount'] ?? 0), 2);

        if (!Schema::hasTable('discount_offers')) {
            return response()->json([
                'status' => true,
                'message' => 'Promo codes retrieved successfully',
                'data' => [
                    'order_amount' => number_format($orderAmount, 2, '.', ''),
                    'promo_codes' => [],
                    'applied_code' => null,
                ],
            ]);
        }

        $customer = $this->resolveCustomer($user);
        if ($customer) {
            CustomerPromoResolver::sanitizeCustomerPromo($customer);
        }
        $appliedCode = CustomerPromoResolver::customerHasExplicitCartPromo($customer)
            ? strtoupper((string) $customer->cart_promo_code)
            : null;

        $offers = DiscountOffer::active()
            ->currentlyValid()
            ->where('apply_to', DiscountOffer::APPLY_ALL)
            ->orderBy('title')
            ->get();

        $promoCodes = $offers->map(function (DiscountOffer $offer) use ($orderAmount) {
            return $this->mapPromoOffer($offer, $orderAmount);
        })->values()->all();

        return response()->json([
            'status' => true,
            'message' => 'Promo codes retrieved successfully',
            'data' => [
                'order_amount' => number_format($orderAmount, 2, '.', ''),
                'promo_codes' => $promoCodes,
                'applied_code' => $appliedCode ? strtoupper((string) $appliedCode) : null,
            ],
        ]);
    }

    /**
     * POST /api/customer-app/promo/apply
     * Body: { "code": "PROMO123", "order_amount": 999 }
     */
    public function apply(Request $request)
    {
        $user = $request->user();
        if (!$this->isAuthorizedCustomer($user)) {
            return response()->json(['status' => false, 'message' => 'Unauthorized customer access'], 403);
        }

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:150'],
            'order_amount' => ['required', 'numeric', 'min:0'],
        ]);

        if (!Schema::hasTable('discount_offers')) {
            return response()->json([
                'status' => false,
                'message' => 'Promo codes are not available',
            ], 503);
        }

        $customer = $this->resolveCustomer($user);
        if (!$customer) {
            return response()->json(['status' => false, 'message' => 'Customer profile not found'], 404);
        }

        CustomerPromoResolver::sanitizeCustomerPromo($customer);

        $orderAmount = round((float) $validated['order_amount'], 2);
        $code = strtoupper(trim($validated['code']));

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

        if (!$offer->cartAmountConditionMet($orderAmount)) {
            $reason = $this->cartAmountFailureReason($offer, $orderAmount);

            return response()->json([
                'status' => false,
                'message' => $reason,
                'data' => [
                    'code' => $code,
                    'order_amount' => number_format($orderAmount, 2, '.', ''),
                    'promo' => $this->mapPromoOffer($offer, $orderAmount),
                ],
            ], 422);
        }

        $discountAmount = $offer->calculateCartDiscount($orderAmount);
        $payableAmount = max(0, round($orderAmount - $discountAmount, 2));

        CustomerPromoResolver::syncCustomerCartPromo($customer, $offer);
        $customer->save();

        return response()->json([
            'status' => true,
            'message' => "Code '{$code}' applied! You saved ₹" . number_format($discountAmount, 2, '.', ''),
            'data' => [
                'code' => $code,
                'offer_id' => $offer->id,
                'order_amount' => number_format($orderAmount, 2, '.', ''),
                'discount_amount' => number_format($discountAmount, 2, '.', ''),
                'payable_amount' => number_format($payableAmount, 2, '.', ''),
                'promo' => $this->mapPromoOffer($offer, $orderAmount, true),
            ],
        ]);
    }

    /**
     * POST /api/customer-app/promo/remove
     */
    public function remove(Request $request)
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
            'data' => [
                'promo_code' => null,
                'promo_applied' => false,
                'has_promo_applied' => false,
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function mapPromoOffer(DiscountOffer $offer, float $orderAmount, ?bool $forceApplicable = null): array
    {
        $isApplicable = $forceApplicable ?? $offer->cartAmountConditionMet($orderAmount);
        $estimatedDiscount = $isApplicable ? $offer->calculateCartDiscount($orderAmount) : 0.0;

        return [
            'id' => $offer->id,
            'code' => strtoupper((string) $offer->title),
            'title' => $offer->title,
            'description' => $offer->description,
            'discount_type' => $offer->discount_type,
            'discount_value' => number_format((float) $offer->discount_value, 2, '.', ''),
            'discount_label' => $offer->discountLabel(),
            'min_cart_amount' => $offer->min_cart_amount !== null
                ? number_format((float) $offer->min_cart_amount, 2, '.', '')
                : null,
            'max_cart_amount' => $offer->max_cart_amount !== null
                ? number_format((float) $offer->max_cart_amount, 2, '.', '')
                : null,
            'valid_from' => $offer->valid_from?->toDateString(),
            'valid_until' => $offer->valid_until?->toDateString(),
            'is_applicable' => $isApplicable,
            'estimated_discount' => number_format($estimatedDiscount, 2, '.', ''),
            'reason' => $isApplicable ? null : $this->cartAmountFailureReason($offer, $orderAmount),
        ];
    }

    private function cartAmountFailureReason(DiscountOffer $offer, float $orderAmount): string
    {
        if ($offer->min_cart_amount !== null && $orderAmount < (float) $offer->min_cart_amount) {
            $min = number_format((float) $offer->min_cart_amount, 2, '.', '');

            return "Minimum order amount ₹{$min} required for this promo";
        }

        if ($offer->max_cart_amount !== null && $orderAmount > (float) $offer->max_cart_amount) {
            $max = number_format((float) $offer->max_cart_amount, 2, '.', '');

            return "This promo is valid only for orders up to ₹{$max}";
        }

        return 'This promo cannot be applied to your order';
    }

    private function isAuthorizedCustomer(?Users $user): bool
    {
        return $user && (int) $user->role_type === self::CUSTOMER_ROLE_TYPE;
    }

    private function resolveCustomer(Users $user): ?Customers
    {
        return Customers::where('user_id', $user->user_id)->first();
    }
}
