<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryAssignmentRejection extends Model
{
    protected $table = 'delivery_assignment_rejections';

    protected $fillable = [
        'assignment_id',
        'driver_id',
        'reason',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(DeliveryAssignment::class, 'assignment_id', 'assignment_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'driver_id', 'user_id');
    }
}
