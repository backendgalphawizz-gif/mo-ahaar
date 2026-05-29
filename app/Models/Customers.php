<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Users;

class Customers extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'customer_id';

    protected $fillable = [
        'user_id',
        'dob',
        'gender',
        'customer_address',
        'latitude',
        'longitude',
        'location_enabled',
        'location_updated_at',
        'cart_cooking_instructions',
        'cart_promo_code',
        'cart_discount_offer_id',
        'cart_selected_address_id',
        'active_cart_vendor_id',
   ];

    public $timestamps = false;

    protected $dates = [
        'created_at',
        'dob',
        'location_updated_at',
    ];

    protected $casts = [
    ];

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id');
    }

    /**
     * Account registration timestamp used to hide pre-signup notifications.
     */
    public function registeredAt(): ?\Illuminate\Support\Carbon
    {
        if (!$this->user_id) {
            return null;
        }

        $createdAt = Users::query()
            ->where('user_id', $this->user_id)
            ->value('created_at');

        return $createdAt ? \Illuminate\Support\Carbon::parse($createdAt) : null;
    }

    public function addresses()
    {
        return $this->hasMany('App\\Models\\CustomerAddress', 'customer_id', 'customer_id');
    }

    public function defaultAddress()
    {
        return $this->hasOne('App\\Models\\CustomerAddress', 'customer_id', 'customer_id')->where('is_default', true);
    }

    public function syncLegacyAddress($address = null): void
    {
        $this->customer_address = $address?->formattedAddress();
        $this->save();
    }
}