<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendor extends Model
{
    protected $table = 'vendors';

    protected $primaryKey = 'vendor_id';

    public $timestamps = true;

    protected $fillable = [
        'vendor_code',
        'user_id',
        'owner_name',
        'mobile',
        'alternate_mobile',
        'email',
        'dob',
        'gender',
        'address',
        'profile_image',
        'business_name',
        'business_type',
        'business_email',
        'business_phone',
        'business_logo',
        'business_banner',
        'shop_image',
        'business_description',
        'latitude',
        'longitude',
        'tax_name',
        'tax_number',
        'pan_number',
        'gst_number',
        'bank_account',
        'account_holder_name',
        'ifsc_code',
        'bank_name',
        'branch_name',
        'account_type',
        'upi_id',
        'commission_percent',
        'wallet_balance',
        'withdrawal_amount',
        'refund_balance',
        'aadhaar_card',
        'pan_card',
        'gst_file',
        'food_license_file',
        'bank_passbook_file',
        'address_proof_file',
        'national_identity_card_file',
        'approval_status',
        'status',
    ];

    protected $casts = [
        'dob' => 'date',
        'latitude' => 'float',
        'longitude' => 'float',
        'commission_percent' => 'decimal:2',
        'wallet_balance' => 'decimal:2',
        'withdrawal_amount' => 'decimal:2',
        'refund_balance' => 'decimal:2',
    ];

    public static function generateVendorCode(): string
    {
        $lastId = (int) static::max('vendor_id');
        $next = $lastId + 1;

        return 'VEND-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'user_id', 'user_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'vendor_id', 'vendor_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Orders::class, 'vendor_id', 'vendor_id');
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(CommissionSettlement::class, 'vendor_id', 'vendor_id');
    }

    public function approvalStatusLabel(): string
    {
        return ucfirst((string) ($this->approval_status ?? 'pending'));
    }

    public function isActive(): bool
    {
        return strtolower((string) ($this->approval_status ?? '')) === 'approved'
            && in_array((string) ($this->status ?? ''), ['1', 'active'], true);
    }
}
