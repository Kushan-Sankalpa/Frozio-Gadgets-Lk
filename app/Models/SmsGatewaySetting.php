<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsGatewaySetting extends Model
{
    protected $fillable = [
        'sender_id',
        'url',
        'api_key',
        'supported_countries',
    ];

    protected $casts = [
        'supported_countries' => 'array',
    ];
}
