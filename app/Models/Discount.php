<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Discount extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $with = ['media'];

    protected $fillable = [
        'name',
        'description',
        'discount_type',
        'discount_amount',
        'priority',
        'starts_at',
        'ends_at',
        'status',
    ];

    public function services()
    {
        return $this->belongsToMany(Service::class, 'discount_service');
    }
}
