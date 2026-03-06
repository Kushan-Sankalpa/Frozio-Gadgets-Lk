<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoeCategory extends Model
{
    use HasFactory;

    protected $table = 'shoes_categories';

    protected $fillable = [
        'name',
        'status',
    ];

    public function subcategories()
    {
        return $this->hasMany(ShoeSubcategory::class, 'category_id');
    }
}