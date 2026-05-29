<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public const GST_EXCLUDED = 'excluded';

    public const GST_INCLUDED = 'included';

    /**
     * @return list<string>
     */
    public static function gstCalculationTypeOptions(): array
    {
        return [self::GST_EXCLUDED, self::GST_INCLUDED];
    }

    protected $table = 'products';
    protected $primaryKey = 'product_id';

    protected $fillable = [
        'product_name',
        'short_description',
        'vendor_id',
        'category_id',
        'sub_category_id',
        'sub_sub_category_id',
        'product_slug',
        'product_type',
        'store_name',
        'unit',
        'weight',
        'product_image',
        'size_chart_image',
        'mrp_price',
        'price',
        'discount',
        'sale_status',
        'sale_start_date',
        'sale_end_date',
        'sku',
        'min_quantity',
        'tags',
        'random_related_product',
        'related_product_ids',
        'cross_sell_product_ids',
        'attribute_ids',
        'video',
        'free_shipping',
        'tax_name',
        'estimated_delivery_text',
        'return_policy_text',
        'featured',
        'safe_checkout',
        'secure_checkout',
        'social_share',
        'encourage_order',
        'encourage_view',
        'trending',
        'is_returnable',
        'is_active_status',
        'status',
        'gst_percentage',
        'gst_calculation_type',
    ];

    /** Not used — kept out of API responses and mass assignment. */
    protected $hidden = [
        'stock',
        'stock_status',
    ];

    public $timestamps = true;

    public function details()
    {
        return $this->hasOne(ProductDetails::class, 'product_id', 'product_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id', 'product_id')
            ->where('status', 1)
            ->orderBy('id');
    }

    /**
     * GST amount calculated from the selling price.
     *  e.g. ₹100 price at 18% → ₹18.00
     */
    public function getGstAmountAttribute(): float
    {
        if (!$this->gstTax) {
            return 0.0;
        }

        $price = (float) $this->price;
        $rate = (float) $this->gstTax->percentage;

        if ($rate <= 0) {
            return 0.0;
        }

        if (($this->gst_calculation_type ?? self::GST_EXCLUDED) === self::GST_INCLUDED) {
            // GST portion already included inside price.
            return round($price - ($price / (1 + ($rate / 100))), 2);
        }

        return round($price * $rate / 100, 2);
    }

    /**
     * Final price including GST.
     *  e.g. ₹100 price at 18% → ₹118.00
     */
    public function getFinalPriceAttribute(): float
    {
        if (($this->gst_calculation_type ?? self::GST_EXCLUDED) === self::GST_INCLUDED) {
            return round((float) $this->price, 2);
        }

        return round((float) $this->price + $this->gst_amount, 2);
    }
}
