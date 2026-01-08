<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LoyaltyPointLog extends Model
{
    protected $fillable = [
        'client_id',
        'admin_id',
        'points_added',
        'note',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
