<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'customer')->get();
        $shippingMethods = ShippingMethod::all();
        $coupons = Coupon::where('is_active', true)->get();

        foreach ($users as $user) {
            // 0-3 orders per user
            $orderCount = rand(0, 3);

            for ($i = 0; $i < $orderCount; $i++) {
                $shippingAddress = Address::where('user_id', $user->id)
                    ->where('address_type', 'shipping')
                    ->first();

                $billingAddress = Address::where('user_id', $user->id)
                    ->where('address_type', 'billing')
                    ->first() ?? $shippingAddress;

                if (!$shippingAddress) continue;

                $shippingMethod = $shippingMethods->random();
                $coupon = $coupons->random();

                $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
                $status = $statuses[array_rand($statuses)];

                $subtotal = rand(100, 500);
                $discountAmount = $coupon && rand(0, 1) ? rand(5, 25) : 0;
                $shippingCost = rand(5, 20);
                $taxAmount = $subtotal * 0.1;

                $total = $subtotal - $discountAmount + $shippingCost + $taxAmount;

                Order::create([
                    'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                    'user_id' => $user->id,
                    'status' => $status,
                    'payment_status' => $status === 'delivered' ? 'paid' : 'pending',
                    'total_amount' => $total,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'shipping_cost' => $shippingCost,
                    'tax_amount' => $taxAmount,
                    'currency_code' => 'USD',
                    'shipping_address_id' => $shippingAddress->id,
                    'billing_address_id' => $billingAddress->id,
                    'shipping_method_id' => $shippingMethod->id,
                    'coupon_id' => $coupon ? $coupon->id : null,
                    'payment_method_id' => PaymentMethod::where('user_id', $user->id)->first()?->id,
                    'notes' => rand(0, 1) ? 'Special delivery instructions: Leave at door' : null,
                    'placed_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }
    }
}
