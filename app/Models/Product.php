<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id','brand_id','model','device_status','price_lkr',
        'discount_type','discount_value',
        'in_stock','stock_count',
        'status','is_featured',
        'warranty_option_id','warranty_period',
        'os',

       'storage_option_ids',
'ram_option_ids',
'color_ids',


        // legacy (optional, if columns still exist)
        'storage_option_id',
        'ram_option_id',

        'display_size','display_type','resolution',
        'rear_camera','front_camera','connectivity','battery_mah',
        'short_description','long_description',
        'main_image_path','gallery_image_paths',
    ];

    protected $casts = [
        'in_stock' => 'boolean',
        'is_featured' => 'boolean',
        'gallery_image_paths' => 'array',

      'storage_option_ids' => 'array',
'ram_option_ids' => 'array',
'color_ids' => 'array',

    ];

    protected $appends = ['main_image_url', 'gallery_urls'];

    public function category() { return $this->belongsTo(Category::class); }
    public function brand() { return $this->belongsTo(Brand::class); }
    public function warrantyOption() { return $this->belongsTo(WarrantyOption::class); }

    public function colors()
    {
        return $this->belongsToMany(ColorOption::class, 'color_option_product', 'product_id', 'color_option_id');
    }

    public function getMainImageUrlAttribute(): ?string
    {
        if (!$this->main_image_path) return null;
        return Storage::disk('public')->url($this->main_image_path);
    }

    public function getGalleryUrlsAttribute(): array
    {
        $paths = $this->gallery_image_paths ?: [];
        return array_values(array_map(fn($p) => Storage::disk('public')->url($p), $paths));
    }
}
