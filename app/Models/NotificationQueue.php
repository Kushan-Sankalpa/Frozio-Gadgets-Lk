<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationQueue extends Model
{
    protected $fillable = [
        'booking_id',
        'client_id',
        'channel',
        'phone',
        'message',
         'type', 
        'status',
        'gateway_status_code',
        'gateway_response',
        'error_message',
        'attempts',
        'scheduled_at',
        'sent_at',
        'last_attempt_at',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'scheduled_at'     => 'datetime',
        'sent_at'          => 'datetime',
        'last_attempt_at'  => 'datetime',
    ];
}
