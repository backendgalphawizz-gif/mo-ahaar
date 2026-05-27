<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverProfile extends Model
{
    protected $table = 'driver_profiles';

    protected $primaryKey = 'profile_id';

    protected $fillable = [
        'driver_id',
        'account_holder_name',
        'bank_name',
        'branch_name',
        'account_number',
        'ifsc_code',
        'account_type',
        'vehicle_number',
        'vehicle_type',
        'vehicle_model',
        'vehicle_color',
        'registration_year',
        'driving_license_number',
        'city',
        'address',
        'driver_code',
        'driving_license',
        'aadhar_card',
        'driving_license_uploaded_at',
        'aadhar_card_uploaded_at',
    ];

    protected $casts = [
        'registration_year' => 'integer',
        'driving_license_uploaded_at' => 'datetime',
        'aadhar_card_uploaded_at' => 'datetime',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'driver_id', 'user_id');
    }
}
