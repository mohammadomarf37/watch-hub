<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class CouponUsageSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::whereNotNull('coupon_id')->get();

        foreach ($orders as $order) {
            if ($order->coupon_id) {
                CouponUsage::create([
                    'coupon_id' => $order->coupon_id,
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'used_at' => $order->placed_at ?? now(),
                ]);

                // Update coupon usage count
                Coupon::where('id', $order->coupon_id)->increment('used_count');
            }
        }
    }
}
