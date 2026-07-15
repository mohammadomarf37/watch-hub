<?php

namespace Database\Seeders;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'customer')->get();

        $providers = ['stripe', 'paypal', 'razorpay', 'paystack'];
        $brands = ['Visa', 'Mastercard', 'Amex', 'Discover'];

        foreach ($users as $index => $user) {
            // Each user has 1-2 payment methods
            $methodCount = rand(1, 2);

            for ($i = 0; $i < $methodCount; $i++) {
                PaymentMethod::create([
                    'user_id' => $user->id,
                    'provider' => $providers[array_rand($providers)],
                    'provider_customer_id' => 'cus_' . strtoupper(substr(md5(rand()), 0, 14)),
                    'payment_method_id' => 'pm_' . strtoupper(substr(md5(rand()), 0, 14)),
                    'last_four' => sprintf('%04d', rand(1000, 9999)),
                    'brand' => $brands[array_rand($brands)],
                    'exp_month' => rand(1, 12),
                    'exp_year' => rand(2025, 2028),
                    'is_default' => $i === 0,
                ]);
            }
        }
    }
}
