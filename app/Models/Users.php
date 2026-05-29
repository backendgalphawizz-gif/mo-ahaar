<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class Users extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * `role_type`: 1 Admin, 2 Customer app, 3 Vendor, 4 Driver (see constants below).
     * `approval_status`: customer registration — pending | approved | rejected (see migration).
     */
    public const CUSTOMER_APP_ROLE_TYPE = 2;

    public const DRIVER_APP_ROLE_TYPE = 4;

    public const STATUS_ACTIVE = 1;

    public const STATUS_INACTIVE = 0;

    public const STATUS_DELETED = 2;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $timestamps = true;

    protected $fillable = [
        'name',
        'email',
        'company_name',
        'gst_number',
        'mobile',
        'login_otp',
        'login_otp_expires_at',
        'password_reset_otp',
        'password_reset_otp_expires_at',
        'profile_image',
        'password',
        'role_type',
        'status',
        'approval_status',
        'accept_terms',
        'terms_accepted_at',
        'gst_verified_at',
        'preferred_language',
        'fcm_id'
    ];

    protected $hidden = [
        'password',
        'login_otp',
        'password_reset_otp',
    ];

    protected $casts = [
        'login_otp_expires_at' => 'datetime',
        'password_reset_otp_expires_at' => 'datetime',
        'gst_verified_at' => 'datetime',
        'terms_accepted_at' => 'datetime',
        'accept_terms' => 'boolean',
    ];

    public function isCustomerAppUser(): bool
    {
        return (int) $this->role_type === self::CUSTOMER_APP_ROLE_TYPE;
    }

    public function isDriverAppUser(): bool
    {
        return (int) $this->role_type === self::DRIVER_APP_ROLE_TYPE;
    }

    public function isDeletedAccount(): bool
    {
        return (int) $this->status === self::STATUS_DELETED;
    }

    public function isDeactivatedAccount(): bool
    {
        return (int) $this->status === self::STATUS_INACTIVE;
    }

    public function isActiveAccount(): bool
    {
        return (int) $this->status === self::STATUS_ACTIVE;
    }

    public function revokeAllApiTokens(): void
    {
        $this->tokens()->delete();
    }

    /**
     * Customer-app users may place orders only when registration is approved and account is active.
     */
    public function canPlaceOrdersAsCustomer(): bool
    {
        return $this->customerOrderRestriction() === null;
    }

    /**
     * @return array{message: string, account_status: string, http_status: int, force_logout: bool}|null
     */
    public function apiAccessRestriction(): ?array
    {
        if ($this->isDeletedAccount()) {
            $this->revokeAllApiTokens();

            return [
                'message' => $this->isDriverAppUser()
                    ? 'Your driver account has been deleted by admin. Please contact support.'
                    : 'Your account has been deleted by admin. Please contact support.',
                'account_status' => 'deleted',
                'http_status' => 403,
                'force_logout' => true,
            ];
        }

        if ($this->isDeactivatedAccount()) {
            return [
                'message' => $this->isDriverAppUser()
                    ? 'Your driver account has been deactivated by admin. Please contact support.'
                    : 'Your account has been deactivated by admin. Please contact support.',
                'account_status' => 'deactivated',
                'http_status' => 403,
                'force_logout' => false,
            ];
        }

        if ($this->isCustomerAppUser()) {
            $approval = strtolower((string) ($this->approval_status ?? 'approved'));

            if ($approval === 'pending') {
                return [
                    'message' => 'Your account is pending admin approval. You cannot use the app until admin activates your account.',
                    'account_status' => 'pending_approval',
                    'http_status' => 403,
                    'force_logout' => false,
                ];
            }

            if ($approval === 'rejected') {
                return [
                    'message' => 'Your account registration was rejected by admin. Please contact support.',
                    'account_status' => 'rejected',
                    'http_status' => 403,
                    'force_logout' => false,
                ];
            }
        }

        if ($this->isDriverAppUser()) {
            $approval = strtolower((string) ($this->approval_status ?? 'approved'));

            if ($approval === 'pending') {
                return [
                    'message' => 'Your driver account is pending admin approval. You cannot use the app until it is activated.',
                    'account_status' => 'pending_approval',
                    'http_status' => 403,
                    'force_logout' => false,
                ];
            }

            if ($approval === 'rejected') {
                return [
                    'message' => 'Your driver account registration was rejected by admin. Please contact support.',
                    'account_status' => 'rejected',
                    'http_status' => 403,
                    'force_logout' => false,
                ];
            }
        }

        return null;
    }

    /**
     * @return array{message: string, account_status: string, http_status: int, force_logout: bool}|null
     */
    public function customerOrderRestriction(): ?array
    {
        if (!$this->isCustomerAppUser()) {
            return [
                'message' => 'Unauthorized customer access.',
                'account_status' => 'not_customer',
                'http_status' => 403,
                'force_logout' => true,
            ];
        }

        if ($access = $this->apiAccessRestriction()) {
            return $access;
        }

        if (!$this->isActiveAccount()) {
            return [
                'message' => 'Your account is not active. Please contact support.',
                'account_status' => $this->customerAccountApprovalLabel(),
                'http_status' => 403,
                'force_logout' => false,
            ];
        }

        return null;
    }

    public function customerAccountApprovalLabel(): string
    {
        if (!$this->isCustomerAppUser()) {
            return 'not_customer';
        }

        if ($this->isDeletedAccount()) {
            return 'deleted';
        }

        if ($this->isDeactivatedAccount()) {
            return 'deactivated';
        }

        $approval = strtolower((string) ($this->approval_status ?? 'approved'));
        if ($approval === 'pending') {
            return 'pending_approval';
        }
        if ($approval === 'rejected') {
            return 'rejected';
        }

        return $this->isActiveAccount() ? 'approved' : 'inactive';
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'user_id', 'user_id');
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to', 'user_id');
    }

    public function ticketReplies(): HasMany
    {
        return $this->hasMany(TicketReply::class, 'user_id', 'user_id');
    }

    public function driverProfile(): HasOne
    {
        return $this->hasOne(DriverProfile::class, 'driver_id', 'user_id');
    }
}