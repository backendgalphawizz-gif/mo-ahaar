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
        'vehicle_number',
        'driving_license',
        'aadhar_card',
        'driving_license_uploaded_at',
        'aadhar_card_uploaded_at',
    ];

    protected $casts = [
        'driving_license_uploaded_at' => 'datetime',
        'aadhar_card_uploaded_at' => 'datetime',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'driver_id', 'user_id');
    }
}
