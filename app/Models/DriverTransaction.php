<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverTransaction extends Model
{
    public const TYPE_CREDIT = 'credit';

    public const TYPE_DEBIT = 'debit';

    public const TYPE_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_PENDING = 'pending';

    public const STATUS_FAILED = 'failed';

    protected $table = 'driver_transactions';

    protected $primaryKey = 'transaction_id';

    protected $fillable = [
        'driver_id',
        'transaction_ref',
        'type',
        'status',
        'amount',
        'balance_after',
        'title',
        'subtitle',
        'order_id',
        'assignment_id',
        'withdrawal_id',
        'meta',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'meta' => 'array',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'driver_id', 'user_id');
    }

    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(DriverWithdrawal::class, 'withdrawal_id', 'withdrawal_id');
    }
}
