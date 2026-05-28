<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryAssignment extends Model
{
    public const STATUS_NEW = 'new';

    public const STATUS_ASSIGNED = 'assigned';

    public const STATUS_REJECTED_BY_DRIVER = 'rejected_by_driver';

    public const STATUS_PICKED_UP = 'picked_up';

    public const STATUS_OUT_FOR_DELIVERY = 'out_for_delivery';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

    public const MODE_MANUAL = 'manual';

    public const MODE_BROADCAST = 'broadcast';

    protected $table = 'delivery_assignments';

    protected $primaryKey = 'assignment_id';

    protected $fillable = [
        'order_id',
        'driver_id',
        'status',
        'payout_amount',
        'pickup_address',
        'delivery_address',
        'store_name',
        'store_image',
        'store_location_summary',
        'reject_reason',
        'assigned_at',
        'completed_at',
        'assignment_mode',
        'broadcast_at',
    ];

    protected $casts = [
        'payout_amount' => 'decimal:2',
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
        'broadcast_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Orders::class, 'order_id', 'order_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'driver_id', 'user_id');
    }

    public function rejections(): HasMany
    {
        return $this->hasMany(DeliveryAssignmentRejection::class, 'assignment_id', 'assignment_id');
    }

    public function invites(): HasMany
    {
        return $this->hasMany(DeliveryAssignmentInvite::class, 'assignment_id', 'assignment_id');
    }

    public static function statusLabel(string $status, bool $forDriverOrders = false): string
    {
        return match ($status) {
            self::STATUS_NEW => 'New',
            self::STATUS_ASSIGNED => $forDriverOrders ? 'Accepted' : 'Assigned',
            self::STATUS_REJECTED_BY_DRIVER => 'Rejected',
            self::STATUS_PICKED_UP => 'Picked Up',
            self::STATUS_OUT_FOR_DELIVERY => 'Out for Delivery',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucwords(str_replace('_', ' ', $status)),
        };
    }

    /**
     * API filter keys exposed on the My Orders screen.
     *
     * @return array<string, string>
     */
    public static function myOrdersFilterMap(): array
    {
        return [
            'accepted' => self::STATUS_ASSIGNED,
            'picked_up' => self::STATUS_PICKED_UP,
            'out_for_delivery' => self::STATUS_OUT_FOR_DELIVERY,
            'delivered' => self::STATUS_DELIVERED,
            'cancelled' => self::STATUS_CANCELLED,
        ];
    }
}
