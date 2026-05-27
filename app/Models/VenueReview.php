<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenueReview extends Model
{
    protected $table = 'venue_reviews';
    protected $primaryKey = 'review_id';

    protected $fillable = [
        'venue_id',
        'customer_id',
        'user_id',
        'booking_id',
        'rating',
        'review',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
        'status' => 'integer',
    ];

    public function venue()
    {
        return $this->belongsTo(Venue::class, 'venue_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customers::class, 'customer_id', 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'user_id');
    }
}
