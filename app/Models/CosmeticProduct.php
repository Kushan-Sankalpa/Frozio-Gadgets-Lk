<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CosmeticProduct extends Model
{
    use HasFactory;

    protected $table = 'cosmetic_products';

    protected $fillable = [
        'name',
        'brand_id',
        'category_id',
        'product_type_id',
        'country_of_origin_id',
        'size_volume_ids',
        'batch_number',
        'manufacture_date',
        'expiry_date',
        'price',
        'stock',
        'discount_type',
        'discount_value',
        'is_featured',
        'hot_deals',
        'best_selling',
        'status',
        'short_description',
        'long_description',
        'main_image_path',
        'gallery_image_paths',
    ];

    protected $casts = [
        'size_volume_ids' => 'array',
        'manufacture_date' => 'date:Y-m-d',
        'expiry_date' => 'date:Y-m-d',
        'price' => 'decimal:2',
        'stock' => 'integer',
        'discount_value' => 'decimal:2',
        'is_featured' => 'boolean',
        'hot_deals' => 'boolean',
        'best_selling' => 'boolean',
        'gallery_image_paths' => 'array',
    ];

    protected $appends = [
        'main_image_url',
        'gallery_urls',
    ];

    public function brand()
    {
        return $this->belongsTo(CosmeticBrand::class, 'brand_id');
    }

    public function category()
    {
        return $this->belongsTo(CosmeticCategory::class, 'category_id');
    }

    public function productType()
    {
        return $this->belongsTo(CosmeticProductType::class, 'product_type_id');
    }

    public function countryOfOrigin()
    {
        return $this->belongsTo(CosmeticCountryOfOrigin::class, 'country_of_origin_id');
    }

    public function variants()
    {
        return $this->hasMany(CosmeticProductVariant::class, 'cosmetic_product_id');
    }

    public function getMainImageUrlAttribute(): ?string
    {
        if (!$this->main_image_path) {
            return null;
        }

        return Storage::disk('public')->url($this->main_image_path);
    }

    public function getGalleryUrlsAttribute(): array
    {
        $paths = $this->gallery_image_paths ?: [];

        return array_values(array_map(
            fn ($p) => Storage::disk('public')->url($p),
            $paths
        ));
    }
}

