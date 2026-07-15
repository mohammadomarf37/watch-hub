<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Watch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'brand_id',
        'category_id',
        'model',
        'slug',
        'description',
        'base_price',
        'discount_percent',
        'discounted_price',
        'stock',
        'rating',
        'rating_count',
        'specifications',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'discounted_price' => 'decimal:2',
        'rating' => 'decimal:2',
        'specifications' => 'json',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function prices()
    {
        return $this->hasMany(WatchPrice::class);
    }

    public function images()
    {
        return $this->hasMany(WatchImage::class);
    }

    public function specifications()
    {
        return $this->hasMany(WatchSpecification::class);
    }

    public function variants()
    {
        return $this->hasMany(WatchVariant::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    // Accessors
    public function getFinalPriceAttribute()
    {
        return $this->discounted_price ?? $this->base_price;
    }

    public function getDiscountPercentageAttribute()
    {
        if ($this->discount_percent > 0) {
            return $this->discount_percent;
        }

        if ($this->discounted_price && $this->base_price > 0) {
            return round((($this->base_price - $this->discounted_price) / $this->base_price) * 100, 2);
        }

        return 0;
    }
}
