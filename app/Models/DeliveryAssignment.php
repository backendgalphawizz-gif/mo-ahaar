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

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_CANCELLED = 'cancelled';

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
    ];

    protected $casts = [
        'payout_amount' => 'decimal:2',
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
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

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_NEW => 'New',
            self::STATUS_ASSIGNED => 'Assigned',
            self::STATUS_REJECTED_BY_DRIVER => 'Rejected',
            self::STATUS_PICKED_UP => 'Picked up',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucwords(str_replace('_', ' ', $status)),
        };
    }
}
