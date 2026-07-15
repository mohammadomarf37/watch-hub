<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WatchVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'watch_id',
        'color',
        'color_hex',
        'size',
        'stock',
        'additional_price',
        'sku',
    ];

    protected $casts = [
        'stock' => 'integer',
        'additional_price' => 'decimal:2',
    ];

    // Relationships
    public function watch()
    {
        return $this->belongsTo(Watch::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlistItems()
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
