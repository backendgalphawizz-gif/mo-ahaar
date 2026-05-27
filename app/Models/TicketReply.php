<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketReply extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'is_admin',
        'is_internal',
        'attachment',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'is_internal' => 'boolean',
    ];

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'user_id');
    }
}