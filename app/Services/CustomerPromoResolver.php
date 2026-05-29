<?php

namespace App\Services;

use App\Models\Customers;
use App\Models\DiscountOffer;
use Illuminate\Support\Facades\Schema;

class CustomerPromoResolver
{
    /**
     * Resolve cart promo from stored customer fields (offer id and/or code).
     *
     * @return array{code: ?string, offer: ?DiscountOffer, discount: float, offer_id: ?int}
     */
    public static function resolve(?Customers $customer, float $eligibleSubtotal): array
    {
        $empty = [
            'code' => null,
            'offer' => null,
            'discount' => 0.0,
            'offer_id' => null,
        ];

        if (!$customer || !Schema::hasTable('discount_offers')) {
            return $empty;
        }

        $storedCode = null;
        if (Schema::hasColumn('customers', 'cart_promo_code')) {
            $raw = trim((string) ($customer->cart_promo_code ?? ''));
            $storedCode = $raw !== '' ? strtoupper($raw) : null;
        }

        $offer = null;
        if (Schema::hasColumn('customers', 'cart_discount_offer_id') && $customer->cart_discount_offer_id) {
            $offer = DiscountOffer::active()->currentlyValid()->find($customer->cart_discount_offer_id);
        }

        if (!$offer && $storedCode) {
            $offer = DiscountOffer::active()
                ->currentlyValid()
                ->whereRaw('UPPER(title) = ?', [$storedCode])
                ->first();
        }

        if (!$offer) {
            return [
                'code' => $storedCode,
                'offer' => null,
                'discount' => 0.0,
                'offer_id' => null,
            ];
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

    public static function customerHasPromoInCart(?Customers $customer): bool
    {
        if (!$customer) {
            return false;
        }

        if (Schema::hasColumn('customers', 'cart_promo_code')
            && trim((string) ($customer->cart_promo_code ?? '')) !== '') {
            return true;
        }

        return Schema::hasColumn('customers', 'cart_discount_offer_id')
            && !empty($customer->cart_discount_offer_id);
    }
}
