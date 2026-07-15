<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\CouponProduct;
use App\Models\Watch;
use Illuminate\Database\Seeder;

class CouponProductSeeder extends Seeder
{
    public function run(): void
    {
        $coupon = Coupon::where('code', 'LUXURY15')->first();
        $luxuryWatches = Watch::whereHas('category', function ($query) {
            $query->where('name', 'Luxury');
        })->get();

        if ($coupon && $luxuryWatches->count() > 0) {
            foreach ($luxuryWatches as $watch) {
                CouponProduct::create([
                    'coupon_id' => $coupon->id,
                    'watch_id' => $watch->id,
                ]);
            }
        }
    }
}
