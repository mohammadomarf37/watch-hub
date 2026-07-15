<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'type' => 'percentage',
                'value' => 10.00,
                'min_order_amount' => 50.00,
                'max_discount' => 25.00,
                'usage_limit' => 100,
                'per_user_limit' => 1,
                'applies_to' => 'all',
                'starts_at' => now(),
                'expires_at' => now()->addMonths(6),
                'is_active' => true,
            ],
            [
                'code' => 'SAVE20',
                'type' => 'percentage',
                'value' => 20.00,
                'min_order_amount' => 100.00,
                'max_discount' => 50.00,
                'usage_limit' => 50,
                'per_user_limit' => 1,
                'applies_to' => 'all',
                'starts_at' => now(),
                'expires_at' => now()->addMonths(3),
                'is_active' => true,
            ],
            [
                'code' => 'FLASH50',
                'type' => 'fixed',
                'value' => 50.00,
                'min_order_amount' => 200.00,
                'max_discount' => null,
                'usage_limit' => 20,
                'per_user_limit' => 1,
                'applies_to' => 'all',
                'starts_at' => now(),
                'expires_at' => now()->addDays(7),
                'is_active' => true,
            ],
            [
                'code' => 'LUXURY15',
                'type' => 'percentage',
                'value' => 15.00,
                'min_order_amount' => 500.00,
                'max_discount' => 100.00,
                'usage_limit' => 30,
                'per_user_limit' => 1,
                'applies_to' => 'specific_categories',
                'starts_at' => now(),
                'expires_at' => now()->addMonths(2),
                'is_active' => true,
            ],
            [
                'code' => 'FREESHIP',
                'type' => 'fixed',
                'value' => 0.00,
                'min_order_amount' => 75.00,
                'max_discount' => null,
                'usage_limit' => 200,
                'per_user_limit' => 2,
                'applies_to' => 'all',
                'starts_at' => now(),
                'expires_at' => now()->addMonths(1),
                'is_active' => true,
            ],
        ];

        foreach ($coupons as $coupon) {
            Coupon::create($coupon);
        }
    }
}
