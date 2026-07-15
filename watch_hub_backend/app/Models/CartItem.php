<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'variant_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // Relationships
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function variant()
    {
        return $this->belongsTo(WatchVariant::class);
    }

    // Helper Methods
    public function getSubtotalAttribute()
    {
        $watch = $this->variant->watch;
        $price = $watch->final_price + $this->variant->additional_price;
        return $price * $this->quantity;
    }
}
