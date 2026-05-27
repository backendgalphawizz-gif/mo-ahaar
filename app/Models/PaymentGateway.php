<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    protected $table = 'payment_gateways';

    protected $fillable = [
        'gateway',
        'display_name',
        'is_enabled',
        'settings',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'settings'   => 'array',
    ];
}
