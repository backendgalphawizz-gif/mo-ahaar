<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    protected $table = 'product_variants';
    protected $primaryKey = 'id';

    protected $fillable = [
        'product_id',
        'variant_label',
        'attribute_combination',
        'price',
        'sale_price',
        'sku',
        'image',
        'status',
    ];

    protected $hidden = [
        'stock',
    ];

    protected $casts = [
        'attribute_combination' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
