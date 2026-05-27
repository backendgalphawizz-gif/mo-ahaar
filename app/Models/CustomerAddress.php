<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    protected $table = 'customer_addresses';
    protected $primaryKey = 'customer_address_id';

    protected $fillable = [
        'customer_id',
        'contact_name',
        'mobile',
        'address_line',
        'landmark',
        'city',
        'state',
        'country',
        'pincode',
        'address_type',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'bool',
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id', 'customer_id');
    }

    public function formattedAddress(): string
    {
        $parts = array_filter([
            $this->address_line,
            $this->landmark,
            $this->city,
            $this->state,
            $this->country,
            $this->pincode,
        ], function ($value) {
            return is_string($value) ? trim($value) !== '' : !empty($value);
        });

        return implode(', ', $parts);
    }
}
