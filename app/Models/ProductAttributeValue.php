<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductAttributeValue extends Model
{
    protected $table = 'attribute_values';
    protected $primaryKey = 'attribute_values_id';

    protected $fillable = [
        'attribute_id',
        'values_title',
        'status',
    ];

    public function getAttributeValueAttribute(): ?string
    {
        return $this->attributes['values_title'] ?? null;
    }

    public function setAttributeValueAttribute($value): void
    {
        $this->attributes['values_title'] = $value;
    }

    public function getAttributeValueIdAttribute(): ?int
    {
        return isset($this->attributes['attribute_values_id']) ? (int) $this->attributes['attribute_values_id'] : null;
    }

    public function attribute()
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id', 'attributes_id');
    }
}
