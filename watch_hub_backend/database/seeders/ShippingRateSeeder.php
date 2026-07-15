<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use Illuminate\Database\Seeder;

class ShippingRateSeeder extends Seeder
{
    public function run(): void
    {
        $standard = ShippingMethod::where('name', 'Standard Shipping')->first();
        $express = ShippingMethod::where('name', 'Express Shipping')->first();
        $overnight = ShippingMethod::where('name', 'Overnight Shipping')->first();

        $rates = [
            // Standard Shipping
            ['shipping_method_id' => $standard->id, 'country_code' => 'US', 'min_order_amount' => 0, 'rate' => 15.00, 'is_free_shipping' => false],
            ['shipping_method_id' => $standard->id, 'country_code' => 'US', 'min_order_amount' => 100, 'rate' => 0, 'is_free_shipping' => true],
            ['shipping_method_id' => $standard->id, 'country_code' => 'PK', 'min_order_amount' => 0, 'rate' => 10.00, 'is_free_shipping' => false],
            ['shipping_method_id' => $standard->id, 'country_code' => 'PK', 'min_order_amount' => 50, 'rate' => 0, 'is_free_shipping' => true],
            ['shipping_method_id' => $standard->id, 'country_code' => 'EU', 'min_order_amount' => 0, 'rate' => 20.00, 'is_free_shipping' => false],
            ['shipping_method_id' => $standard->id, 'country_code' => 'EU', 'min_order_amount' => 150, 'rate' => 0, 'is_free_shipping' => true],

            // Express Shipping
            ['shipping_method_id' => $express->id, 'country_code' => 'US', 'min_order_amount' => 0, 'rate' => 35.00, 'is_free_shipping' => false],
            ['shipping_method_id' => $express->id, 'country_code' => 'PK', 'min_order_amount' => 0, 'rate' => 25.00, 'is_free_shipping' => false],
            ['shipping_method_id' => $express->id, 'country_code' => 'EU', 'min_order_amount' => 0, 'rate' => 40.00, 'is_free_shipping' => false],

            // Overnight Shipping
            ['shipping_method_id' => $overnight->id, 'country_code' => 'US', 'min_order_amount' => 0, 'rate' => 60.00, 'is_free_shipping' => false],
            ['shipping_method_id' => $overnight->id, 'country_code' => 'PK', 'min_order_amount' => 0, 'rate' => 45.00, 'is_free_shipping' => false],
            ['shipping_method_id' => $overnight->id, 'country_code' => 'EU', 'min_order_amount' => 0, 'rate' => 70.00, 'is_free_shipping' => false],
        ];

        foreach ($rates as $rate) {
            ShippingRate::create($rate);
        }
    }
}
