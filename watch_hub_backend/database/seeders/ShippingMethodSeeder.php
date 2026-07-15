<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name' => 'Standard Shipping',
                'description' => 'Standard delivery within 5-10 business days',
                'delivery_days_min' => 5,
                'delivery_days_max' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Express Shipping',
                'description' => 'Express delivery within 2-4 business days',
                'delivery_days_min' => 2,
                'delivery_days_max' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Overnight Shipping',
                'description' => 'Next day delivery',
                'delivery_days_min' => 1,
                'delivery_days_max' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($methods as $method) {
            ShippingMethod::create($method);
        }
    }
}
