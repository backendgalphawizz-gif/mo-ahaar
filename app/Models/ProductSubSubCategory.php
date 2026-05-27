<?php

namespace App\Models;
use App\Models\ProductCategory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class ProductSubSubCategory extends Authenticatable
{
    protected $table = 'sub_sub_categories';
    protected $primaryKey = 'sub_sub_category_id';
    public $timestamps = true;

    protected $fillable = [
        'sub_sub_category_name',
        'sub_sub_description',
        'sub_sub_cat_image',
        'sub_sub_slug',
        'status'
    ];

    public function subCategory()
    {
        return $this->belongsTo(ProductSubCategory::class, 'sub_category_id', 'sub_category_id');
    }
}