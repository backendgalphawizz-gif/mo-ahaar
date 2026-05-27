<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformFeedback extends Model
{
    protected $table = 'platform_feedback';
    protected $primaryKey = 'feedback_id';

    protected $fillable = [
        'customer_id',
        'user_id',
        'rating',
        'category',
        'feedback_text',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
        'status' => 'integer',
    ];

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id', 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'user_id');
    }
}
