<?php

namespace App\Models;
use App\Models\ProductCategory;
use App\Models\Product;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class ProductSubCategory extends Authenticatable
{
    protected $table = 'sub_categories';
    protected $primaryKey = 'sub_category_id';
    public $timestamps = true;

    protected $fillable = [
        'sub_cat_name',
        'sub_cat_description',
        'sub_cat_image',
        'sub_cat_slug',
        'status'
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id', 'category_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'sub_category_id', 'sub_category_id');
    }


}