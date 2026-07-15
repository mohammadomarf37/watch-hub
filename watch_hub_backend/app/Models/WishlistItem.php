<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WishlistItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'wishlist_id',
        'variant_id',
    ];

    // Relationships
    public function wishlist()
    {
        return $this->belongsTo(Wishlist::class);
    }

    public function variant()
    {
        return $this->belongsTo(WatchVariant::class);
    }
}
