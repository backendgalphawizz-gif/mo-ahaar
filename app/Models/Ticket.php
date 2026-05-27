<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    public const STATUS_OPEN = 'open';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_WAITING_ON_USER = 'waiting_on_user';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    public const PRIORITY_LOW = 'low';
    public const PRIORITY_MEDIUM = 'medium';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    protected $fillable = [
        'user_id',
        'type',
        'subject',
        'description',
        'status',
        'priority',
        'assigned_to',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_OPEN,
            self::STATUS_IN_PROGRESS,
            self::STATUS_WAITING_ON_USER,
            self::STATUS_RESOLVED,
            self::STATUS_CLOSED,
        ];
    }

    public static function priorityOptions(): array
    {
        return [
            self::PRIORITY_LOW,
            self::PRIORITY_MEDIUM,
            self::PRIORITY_HIGH,
            self::PRIORITY_URGENT,
        ];
    }

    public static function typeOptions(): array
    {
        return [
            'general',
            'order',
            'payment',
            'technical',
            'account',
            'other',
        ];
    }

    public function user()
    {
        return $this->belongsTo(Users::class, 'user_id', 'user_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(Users::class, 'assigned_to', 'user_id');
    }

    public function replies()
    {
        return $this->hasMany(TicketReply::class)->orderBy('created_at');
    }

    public function attachments()
    {
        return $this->hasMany(TicketAttachment::class)->orderByDesc('id');
    }
}