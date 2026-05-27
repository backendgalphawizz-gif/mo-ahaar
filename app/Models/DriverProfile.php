<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverProfile extends Model
{
    public const DOCUMENT_PAN = 'pan';

    public const DOCUMENT_AADHAR = 'aadhar';

    protected $table = 'driver_profiles';

    protected $primaryKey = 'profile_id';

    protected $fillable = [
        'driver_id',
        'driver_code',
        'document_type',
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
        'pan_card',
        'aadhar_card',
        'aadhar_card_back',
        'driving_license',
        'rc_image',
        'puc_number',
        'puc_expiry_date',
        'puc_image',
        'pan_card_uploaded_at',
        'aadhar_card_uploaded_at',
        'aadhar_card_back_uploaded_at',
        'driving_license_uploaded_at',
        'rc_image_uploaded_at',
        'puc_image_uploaded_at',
    ];

    protected $casts = [
        'registration_year' => 'integer',
        'puc_expiry_date' => 'date',
        'pan_card_uploaded_at' => 'datetime',
        'aadhar_card_uploaded_at' => 'datetime',
        'aadhar_card_back_uploaded_at' => 'datetime',
        'driving_license_uploaded_at' => 'datetime',
        'rc_image_uploaded_at' => 'datetime',
        'puc_image_uploaded_at' => 'datetime',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Users::class, 'driver_id', 'user_id');
    }

    public function identityDocumentFile(): ?string
    {
        return $this->document_type === self::DOCUMENT_PAN
            ? $this->pan_card
            : $this->aadhar_card;
    }

    public function hasCompleteIdentityDocuments(): bool
    {
        if ($this->document_type === self::DOCUMENT_PAN) {
            return !empty($this->pan_card);
        }

        if ($this->document_type === self::DOCUMENT_AADHAR) {
            return !empty($this->aadhar_card) && !empty($this->aadhar_card_back);
        }

        return false;
    }

    public function identityDocumentUploadedAt()
    {
        return $this->document_type === self::DOCUMENT_PAN
            ? $this->pan_card_uploaded_at
            : $this->aadhar_card_uploaded_at;
    }
}
