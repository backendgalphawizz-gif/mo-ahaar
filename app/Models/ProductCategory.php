<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    protected $table = 'product_categories';
    protected $primaryKey = 'category_id';
    protected $fillable = [
        'category_name',
        'slug',
        'category_description',
        'category_image',
        'status'
    ];

    public function subCategories()
    {
        return $this->hasMany(ProductSubCategory::class, 'category_id', 'category_id');
    }
}