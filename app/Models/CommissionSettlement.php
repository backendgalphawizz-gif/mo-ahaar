<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionSettlement extends Model
{
    protected $table = 'commission_settlements';
    protected $primaryKey = 'settlement_id';

    protected $fillable = [
        'vendor_id',
        'period_start',
        'period_end',
        'gross_sales',
        'commission_rate',
        'commission_amount',
        'payout_amount',
        'status',
        'request_note',
        'admin_note',
        'requested_at',
        'processed_at',
        'paid_at',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'gross_sales' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'payout_amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id', 'vendor_id');
    }
}
