<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Branch extends Model  implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $with = ['media'];

    protected $fillable = [
        
        'name',
        'email',
        'contact_number',
        'business_located',
        'company_name',
        'address',
        'city',
        'state',
        'postcode',
        'latitude',
        'longitude',
        'opening_hours',
    ];
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'opening_hours' => 'array',
    ];


    public function user()
    {
        return $this->belongsToMany(User::class, 'user_branch');
    }
}
