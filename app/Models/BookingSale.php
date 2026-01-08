<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingSale extends Model
{
    protected $fillable = [
        'booking_id',
        'branch_id',
        'payment_method',
        'base_amount',
        'tax_amount',
        'tip_amount',
        'total_with_tip',
        'tip_staff_id',
        'total_paid',
        'remaining',
          'tips_json',
        'payments_json',
        'services_json',
    ];

    protected $casts = [
        'payments_json' => 'array',
        'services_json' => 'array',
         'tips_json'     => 'array',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
