<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceVariant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'service_id', 'name', 'description',
        'price_type', 'price', 'duration_minutes',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }
      public function bookingServices(): HasMany
    {
        return $this->hasMany(BookingService::class, 'service_variant_id');
    }
}
