<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductDetails extends Model
{
    protected $table = 'product_details';
    protected $primaryKey = 'product_details_id';

    protected $fillable = [
        'product_id',
        'gallery_images',
        'product_description',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    public $timestamps = false;
}