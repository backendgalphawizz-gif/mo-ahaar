<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DiscountOffer extends Model
{
    protected $table = 'discount_offers';

    protected $fillable = [
        'title',
        'description',
        'discount_type',
        'discount_value',
        'apply_to',
        'product_ids',
        'category_ids',
        'valid_from',
        'valid_until',
        'min_quantity',
        'max_quantity',
        'min_cart_amount',
        'max_cart_amount',
        'is_active',
    ];

    protected $casts = [
        'product_ids'     => 'array',
        'category_ids'    => 'array',
        'discount_value'  => 'decimal:2',
        'min_cart_amount' => 'decimal:2',
        'max_cart_amount' => 'decimal:2',
        'valid_from'      => 'date',
        'valid_until'     => 'date',
        'is_active'       => 'integer',
        'min_quantity'    => 'integer',
        'max_quantity'    => 'integer',
    ];

    public $timestamps = true;

    // -------------------------------------------------------------------------
    // Constants
    // -------------------------------------------------------------------------
    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED      = 'fixed';

    public const APPLY_ALL        = 'all';
    public const APPLY_PRODUCTS   = 'specific_products';
    public const APPLY_CATEGORIES = 'specific_categories';

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    /**
     * Offers that are currently valid (or have no date restriction).
     */
    public function scopeCurrentlyValid($query)
    {
        $today = Carbon::today()->toDateString();

        return $query->where(function ($q) use ($today) {
            $q->whereNull('valid_from')->orWhere('valid_from', '<=', $today);
        })->where(function ($q) use ($today) {
            $q->whereNull('valid_until')->orWhere('valid_until', '>=', $today);
        });
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Returns true if this offer applies to the given product (by product_id or category_id).
     */
    public function appliesToProduct(int $productId, ?int $categoryId = null): bool
    {
        if ($this->apply_to === self::APPLY_ALL) {
            return true;
        }

        if ($this->apply_to === self::APPLY_PRODUCTS) {
            return in_array($productId, (array) ($this->product_ids ?? []), false);
        }

        if ($this->apply_to === self::APPLY_CATEGORIES && $categoryId !== null) {
            return in_array($categoryId, (array) ($this->category_ids ?? []), false);
        }

        return false;
    }

    /**
     * Calculates the discount amount for a given quantity and unit price.
     * Respects min/max quantity conditions.
     * Returns 0 if conditions are not met.
     */
    public function calculateLineDiscount(int $quantity, float $unitPrice): float
    {
        if ($this->min_quantity !== null && $quantity < $this->min_quantity) {
            return 0.0;
        }
        if ($this->max_quantity !== null && $quantity > $this->max_quantity) {
            return 0.0;
        }

        $lineTotal = $quantity * $unitPrice;

        if ($this->discount_type === self::TYPE_PERCENTAGE) {
            return round($lineTotal * ((float) $this->discount_value / 100), 2);
        }

        // Fixed discount capped at line total
        return round(min((float) $this->discount_value, $lineTotal), 2);
    }

    /**
     * Returns true if the cart subtotal satisfies the cart-amount conditions.
     */
    public function cartAmountConditionMet(float $cartSubtotal): bool
    {
        if ($this->min_cart_amount !== null && $cartSubtotal < (float) $this->min_cart_amount) {
            return false;
        }
        if ($this->max_cart_amount !== null && $cartSubtotal > (float) $this->max_cart_amount) {
            return false;
        }

        return true;
    }

    /**
     * Discount amount on cart subtotal (percentage or fixed, capped at subtotal).
     */
    public function calculateCartDiscount(float $cartSubtotal): float
    {
        if ($cartSubtotal <= 0) {
            return 0.0;
        }

        if ($this->discount_type === self::TYPE_PERCENTAGE) {
            return round($cartSubtotal * ((float) $this->discount_value / 100), 2);
        }

        return round(min((float) $this->discount_value, $cartSubtotal), 2);
    }

    /**
     * Human-readable savings label for mobile UI.
     */
    public function discountLabel(): string
    {
        if ($this->discount_type === self::TYPE_PERCENTAGE) {
            return rtrim(rtrim(number_format((float) $this->discount_value, 2, '.', ''), '0'), '.') . '% off';
        }

        return '₹' . number_format((float) $this->discount_value, 2, '.', '') . ' off';
    }
}
