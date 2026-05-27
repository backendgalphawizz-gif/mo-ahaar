<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DriverWallet extends Model
{
    protected $table = 'driver_wallets';

    protected $primaryKey = 'wallet_id';

    protected $fillable = [
        'driver_id',
        'balance',
        'pending_balance',
        'currency',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'driver_id', 'user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(DriverTransaction::class, 'driver_id', 'driver_id');
    }

    public function availableBalance(): float
    {
        return max(0, (float) $this->balance - (float) $this->pending_balance);
    }
}
