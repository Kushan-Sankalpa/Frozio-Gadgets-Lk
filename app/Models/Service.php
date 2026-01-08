<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'service_category_id',
        'name',
        'description',
        'price_type',
        'price',
        'duration_minutes',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function variants()
    {
        return $this->hasMany(ServiceVariant::class, 'service_id');
    }


    public function user()
    {
        return $this->belongsToMany(User::class, 'service_user');
    }

    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'discount_service');
    }
}
