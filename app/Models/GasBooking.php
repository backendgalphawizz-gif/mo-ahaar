<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GasBooking extends Model
{
    protected $table = 'gas_bookings';
    protected $primaryKey = 'gas_booking_id';

    protected $fillable = [
        'customer_name',
        'mobile_number',
        'provider',
        'consumer_number',
        'booking_ref',
        'status',
        'booked_at',
        'delivery_eta',
    ];
}
