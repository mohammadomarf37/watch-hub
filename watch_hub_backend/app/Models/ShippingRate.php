<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShippingRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipping_method_id',
        'country_code',
        'min_order_amount',
        'rate',
        'is_free_shipping',
    ];

    protected $casts = [
        'min_order_amount' => 'decimal:2',
        'rate' => 'decimal:2',
        'is_free_shipping' => 'boolean',
    ];

    // Relationships
    public function shippingMethod()
    {
        return $this->belongsTo(ShippingMethod::class);
    }
}
