<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class DriverWithdrawal extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $table = 'driver_withdrawals';

    protected $primaryKey = 'withdrawal_id';

    protected $fillable = [
        'driver_id',
        'amount',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'driver_id', 'user_id');
    }

    public function transaction(): HasOne
    {
        return $this->hasOne(DriverTransaction::class, 'withdrawal_id', 'withdrawal_id');
    }
}
