<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WatchPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'watch_id',
        'currency_id',
        'price',
        'discounted_price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discounted_price' => 'decimal:2',
    ];

    // Relationships
    public function watch()
    {
        return $this->belongsTo(Watch::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
