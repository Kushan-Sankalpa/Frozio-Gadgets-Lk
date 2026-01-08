<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
// optional: conversions
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class ServiceCategory extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name', 'description', 'color_code', 'sort_order', 'status',
    ];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('category_image')
            ->singleFile(); 
    }

}
