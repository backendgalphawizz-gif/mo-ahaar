<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';
    protected $primaryKey = 'item_id';

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'sku',
        'quantity',
        'unit_price',
        'discount_amount',
        'tax_amount',
        'gst_amount',
        'gst_percentage',
        'gst_calculation_type',
        'effective_price',
        'line_total',
        'item_status',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'gst_percentage' => 'decimal:2',
        'effective_price' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id', 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
