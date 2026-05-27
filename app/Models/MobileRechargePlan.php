<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileRechargePlan extends Model
{
    protected $table = 'mobile_recharge_plans';
    protected $primaryKey = 'plan_id';

    protected $fillable = [
        'operator',
        'plan_name',
        'amount',
        'validity_days',
        'benefits',
        'status',
    ];
}
