<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WatchSpecification extends Model
{
    use HasFactory;

    protected $fillable = [
        'watch_id',
        'spec_key',
        'spec_value',
    ];

    // Relationships
    public function watch()
    {
        return $this->belongsTo(Watch::class);
    }
}
