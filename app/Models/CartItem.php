<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $table = 'cart_items';
    protected $primaryKey = 'cart_item_id';

    protected $fillable = [
        'customer_id',
        'product_id',
        'quantity',
        'unit_price',
        'sale_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
