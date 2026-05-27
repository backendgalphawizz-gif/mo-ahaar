<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FastagRecharge extends Model
{
    protected $table = 'fastag_recharges';
    protected $primaryKey = 'fastag_recharge_id';

    protected $fillable = [
        'fastag_account_id',
        'amount',
        'transaction_ref',
        'status',
        'recharged_at',
    ];
}
