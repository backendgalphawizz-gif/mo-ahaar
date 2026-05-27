<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Users extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * `role_type`: 1 Admin, 2 customer-app user or vendor depending on product routing (customer API uses 2).
     * `user_type`: Retailer / Wholesaler for customer accounts — separate from role_type.
     * `approval_status`: customer registration — pending | approved | rejected (see migration).
     */
    public const CUSTOMER_APP_ROLE_TYPE = 2;

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
        'user_type',
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

    /**
     * Customer-app users may place orders only when registration is approved and account is active.
     */
    public function canPlaceOrdersAsCustomer(): bool
    {
        if (!$this->isCustomerAppUser()) {
            return false;
        }

        $approval = strtolower((string) ($this->approval_status ?? 'approved'));
        if ($approval !== 'approved') {
            return false;
        }

        return (int) $this->status === 1;
    }

    public function customerAccountApprovalLabel(): string
    {
        if (!$this->isCustomerAppUser()) {
            return 'not_customer';
        }

        $approval = strtolower((string) ($this->approval_status ?? 'approved'));
        if ($approval === 'pending') {
            return 'pending_approval';
        }
        if ($approval === 'rejected') {
            return 'rejected';
        }

        return match ((int) $this->status) {
            1 => 'approved',
            2 => 'deleted',
            default => 'inactive',
        };
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
}