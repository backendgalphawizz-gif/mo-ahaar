<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'order_id';

    /**
     * Fulfillment statuses admins set from the orders list and order detail (quick update).
     */
    public static function adminPrimaryFulfillmentStatuses(): array
    {
        return [
            // 'processing' => 'Processing',
            // 'shipped' => 'Shipped',
            'processing' => 'Ready to dispatch',
            'shipped' => 'Out for delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
        ];
    }

    /**
     * Any order_status value that may exist in the database or create/edit order forms.
     */
    public static function persistableOrderStatuses(): array
    {
        return array_values(array_unique(array_merge(
            array_keys(self::adminPrimaryFulfillmentStatuses()),
            [
                'pending',
                'confirmed',
                'payment_pending',
                'accepted',
                'rejected',
                'out_for_delivery',
                'completed',
                'success',
            ]
        )));
    }

    public static function statusLabel(?string $status): string
    {
        if ($status === null || $status === '') {
            return 'Unknown';
        }

        $key = strtolower(trim($status));
        $primary = self::adminPrimaryFulfillmentStatuses();

        if (isset($primary[$key])) {
            return $primary[$key];
        }

        return match ($key) {
            'pending', 'payment_pending' => 'Pending',
            'confirmed', 'accepted' => 'Confirmed',
            'picked_up' => 'Ready to dispatch',
            'out_for_delivery' => 'Out for delivery',
            'rejected' => 'Rejected',
            'completed', 'success' => 'Completed',
            default => ucwords(str_replace('_', ' ', $key)),
        };
    }

    protected $fillable = [
        'customer_id',
        'vendor_id',
        'order_number',
        'subtotal',
        'tax_amount',
        'gst_amount',
        'shipping_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'order_status',
        'shipping_address',
        'notes',
        'razorpay_order_id',
        'razorpay_payment_id',
        'razorpay_signature',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'shipping_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id', 'customer_id');
    }


    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'vendor_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }

    public function trackings()
    {
        return $this->hasMany(OrderTracking::class, 'order_id', 'order_id')->orderByDesc('tracked_at');
    }
}