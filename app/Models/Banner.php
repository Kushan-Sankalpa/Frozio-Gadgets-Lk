<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Banner extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    
    protected $with = ['media'];

    protected $fillable = [
        'banner_name',
        'status',
        'banner_description',
    ];
}
