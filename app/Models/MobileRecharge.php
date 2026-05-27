<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileRecharge extends Model
{
    protected $table = 'mobile_recharges';
    protected $primaryKey = 'mobile_recharge_id';

    protected $fillable = [
        'mobile_number',
        'operator',
        'plan_id',
        'amount',
        'transaction_ref',
        'status',
        'recharged_at',
    ];
}
