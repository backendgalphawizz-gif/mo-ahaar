<?php

namespace App\Services;

use App\Models\Customers;
use App\Models\DiscountOffer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class CustomerPromoResolver
{
    /**
     * Cart coupon applied only when user called apply-promo (offer id stored on customer).
     *
     * @return array{code: ?string, offer: ?DiscountOffer, discount: float, offer_id: ?int}
     */
    public static function resolveExplicitCartPromo(?Customers $customer, float $eligibleSubtotal): array
    {
        $empty = [
            'code' => null,
            'offer' => null,
            'discount' => 0.0,
            'offer_id' => null,
        ];

        if (
            !$customer
            || !Schema::hasTable('discount_offers')
            || !Schema::hasColumn('customers', 'cart_discount_offer_id')
            || empty($customer->cart_discount_offer_id)
        ) {
            return $empty;
        }

        $offer = DiscountOffer::active()
            ->currentlyValid()
            ->find($customer->cart_discount_offer_id);

        if (!$offer) {
            return $empty;
        }

        $code = strtoupper((string) $offer->title);
        $discount = 0.0;

        if ($offer->cartAmountConditionMet($eligibleSubtotal)) {
            $discount = $offer->calculateCartDiscount(max(0, $eligibleSubtotal));
        }

        return [
            'code' => $code,
            'offer' => $offer,
            'discount' => round($discount, 2),
            'offer_id' => $offer->id,
        ];
    }

    public static function syncCustomerCartPromo(Customers $customer, DiscountOffer $offer): void
    {
        if (Schema::hasColumn('customers', 'cart_promo_code')) {
            $customer->cart_promo_code = strtoupper((string) $offer->title);
        }
        if (Schema::hasColumn('customers', 'cart_discount_offer_id')) {
            $customer->cart_discount_offer_id = $offer->id;
        }
    }

    public static function clearCustomerCartPromo(Customers $customer): void
    {
        if (Schema::hasColumn('customers', 'cart_promo_code')) {
            $customer->cart_promo_code = null;
        }
        if (Schema::hasColumn('customers', 'cart_discount_offer_id')) {
            $customer->cart_discount_offer_id = null;
        }
    }

    /**
     * Drop stale promo code text that was never applied via apply-promo.
     */
    public static function sanitizeCustomerPromo(Customers $customer): void
    {
        $hasOfferId = Schema::hasColumn('customers', 'cart_discount_offer_id')
            && !empty($customer->cart_discount_offer_id);
        $hasCode = Schema::hasColumn('customers', 'cart_promo_code')
            && trim((string) ($customer->cart_promo_code ?? '')) !== '';

        if ($hasCode && !$hasOfferId) {
            $customer->cart_promo_code = null;
            $customer->save();
        }
    }

    public static function customerHasExplicitCartPromo(?Customers $customer): bool
    {
        return $customer
            && Schema::hasColumn('customers', 'cart_discount_offer_id')
            && !empty($customer->cart_discount_offer_id);
    }

    /**
     * Product-level automatic discounts (excludes cart coupon offers with apply_to = all).
     *
     * @param  Collection<int, mixed>  $cartItems  CartItem models with product relation loaded
     * @return array{discount: float, applied: list<array<string, mixed>>}
     */
    public static function calculateAutomaticLineOfferDiscounts(Collection $cartItems, float $cartSubtotalForConditions): array
    {
        if (!Schema::hasTable('discount_offers') || $cartItems->isEmpty()) {
            return ['discount' => 0.0, 'applied' => []];
        }

        $offers = DiscountOffer::active()
            ->currentlyValid()
            ->where('apply_to', '!=', DiscountOffer::APPLY_ALL)
            ->get();

        $appliedOffers = [];

        foreach ($cartItems as $item) {
            $product = $item->product;
            if (!$product) {
                continue;
            }

            $unitPrice = (float) ($item->sale_price ?: $item->unit_price);
            $qty = (int) $item->quantity;
            $categoryId = $product->category_id ? (int) $product->category_id : null;

            foreach ($offers as $offer) {
                if (!$offer->appliesToProduct((int) $product->product_id, $categoryId)) {
                    continue;
                }

                $lineDiscount = $offer->calculateLineDiscount($qty, $unitPrice);
                if ($lineDiscount <= 0) {
                    continue;
                }

                $appliedOffers[$offer->id] = [
                    'offer_id' => $offer->id,
                    'title' => $offer->title,
                    'type' => $offer->discount_type,
                    'value' => number_format((float) $offer->discount_value, 2, '.', ''),
                ];
            }
        }

        foreach ($offers as $offer) {
            if (!$offer->cartAmountConditionMet($cartSubtotalForConditions)) {
                unset($appliedOffers[$offer->id]);
            }
        }

        $totalDiscount = 0.0;
        foreach ($cartItems as $item) {
            $product = $item->product;
            if (!$product) {
                continue;
            }

            $unitPrice = (float) ($item->sale_price ?: $item->unit_price);
            $qty = (int) $item->quantity;
            $categoryId = $product->category_id ? (int) $product->category_id : null;

            foreach ($offers as $offer) {
                if (!isset($appliedOffers[$offer->id])) {
                    continue;
                }
                if (!$offer->appliesToProduct((int) $product->product_id, $categoryId)) {
                    continue;
                }
                $totalDiscount += $offer->calculateLineDiscount($qty, $unitPrice);
            }
        }

        return [
            'discount' => round($totalDiscount, 2),
            'applied' => array_values($appliedOffers),
        ];
    }
}
