<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'variant_id',
        'watch_id',
        'quantity',
        'price',
        'discount_applied',
        'total',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_applied' => 'decimal:2',
        'total' => 'decimal:2',
        'quantity' => 'integer',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function variant()
    {
        return $this->belongsTo(WatchVariant::class);
    }

    public function watch()
    {
        return $this->belongsTo(Watch::class);
    }
}
