<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerNotification extends Model
{
    protected $table = 'customer_notifications';
    protected $primaryKey = 'notification_id';

    protected $fillable = [
        'customer_id',
        'source_type',
        'source_id',
        'order_id',
        'title',
        'message',
        'meta',
        'is_read',
        'read_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id', 'customer_id');
    }

    public function order()
    {
        return $this->belongsTo(Orders::class, 'order_id', 'order_id');
    }
}
