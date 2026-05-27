<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FastagAccount extends Model
{
    protected $table = 'fastag_accounts';
    protected $primaryKey = 'fastag_account_id';

    protected $fillable = [
        'vehicle_number',
        'provider',
        'tag_id',
        'current_balance',
        'status',
    ];
}
