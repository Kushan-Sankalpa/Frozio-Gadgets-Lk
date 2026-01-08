<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class LoyaltyTier extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'start_points',
        'end_points',
        'priority',
        'benefits',
    ];

    protected $casts = [
        'benefits' => 'array',
    ];


}
