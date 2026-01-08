<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Client extends Authenticatable implements HasMedia
{
    use HasApiTokens;
    use HasFactory;
    use InteractsWithMedia;
    use Notifiable;

    protected $with = ['media'];
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'phone_code',
        'birthday_daymonth',
        'birthday_year',
        'gender',
        'country_id',
        'address_type',
        'address',
        'district',
        'city',
        'postcode',
        'address_country_id',
        'address_country',
        'password',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
    public function loyaltyTier()
    {
        return $this->belongsTo(LoyaltyTier::class, 'loyalty_tier_id');
    }
    public function pointLogs()
    {
        return $this->hasMany(LoyaltyPointLog::class);
    }
}
