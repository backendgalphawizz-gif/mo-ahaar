<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GstTax extends Model
{
    protected $table = 'gst_taxes';

    protected $fillable = [
        'name',
        'percentage',
        'status',
    ];

    protected $casts = [
        'percentage' => 'decimal:2',
        'status'     => 'integer',
    ];

    public $timestamps = true;

    /**
     * Scope: only active GST taxes.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Human-readable label used in dropdowns.
     *  e.g. "GST 18% (18.00%)"
     */
    public function getLabelAttribute(): string
    {
        return $this->name . ' (' . number_format((float) $this->percentage, 2) . '%)';
    }
}
