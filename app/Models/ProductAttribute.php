<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttribute extends Model
{
    protected $table = 'attributes';
    protected $primaryKey = 'attributes_id';
    protected $fillable = [
        'attributes_name',
        'status'
    ];

    public function values()
    {
        return $this->hasMany(ProductAttributeValue::class, 'attribute_id', 'attributes_id');
    }
}