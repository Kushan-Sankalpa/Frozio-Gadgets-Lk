<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingService extends Model
{

     protected $with = ['staff'];

    protected $fillable = [
        'booking_id',
        'service_id',
        'service_variant_id',
        'staff_id',
        'label',
        'duration_minutes',
        'extra_minutes',
        'starts_at',
        'ends_at',
        'price',
        'discount_type',
        'discount_value',
        'final_price',
        'color_code',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at'   => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ServiceVariant::class, 'service_variant_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
