<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoeSubcategory extends Model
{
    use HasFactory;

    protected $table = 'shoe_subcategories';

    protected $fillable = [
        'category_id',
        'name',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(ShoeCategory::class, 'category_id');
    }
}