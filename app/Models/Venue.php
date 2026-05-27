<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    protected $table = 'venues';
    protected $primaryKey = 'id';

    protected $fillable = [
        'vendor_id',
        'name',
        'address',
        'city',
        'capacity',
        'price_per_day',
        'image',
        'gallery_images',
        'status',
        'description',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'price_per_day' => 'decimal:2',
        'status' => 'integer',
        'gallery_images' => 'array',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'vendor_id');
    }
}
