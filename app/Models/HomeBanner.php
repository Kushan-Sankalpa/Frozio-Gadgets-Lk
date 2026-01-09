<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HomeBanner extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'video_path',
        'description',
    ];

    protected $appends = ['video_url'];

    public function getVideoUrlAttribute(): ?string
    {
        if (!$this->video_path) return null;
        return Storage::disk('public')->url($this->video_path);
    }
}
