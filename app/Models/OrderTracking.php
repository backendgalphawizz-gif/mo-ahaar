<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderTracking extends Model
{
    protected $table = 'order_trackings';
    protected $primaryKey = 'tracking_id';

    protected $fillable = [
        'order_id',
        'status',
        'location',
        'description',
        'tracked_at',
    ];

    protected $casts = [
        'tracked_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id', 'order_id');
    }
}
