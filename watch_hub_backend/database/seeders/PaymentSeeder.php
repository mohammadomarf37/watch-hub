<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::where('payment_status', 'paid')->get();

        foreach ($orders as $order) {
            $statuses = ['completed', 'completed', 'completed', 'failed'];

            Payment::create([
                'order_id' => $order->id,
                'payment_method_id' => PaymentMethod::where('user_id', $order->user_id)->first()?->id,
                'transaction_id' => 'txn_' . strtoupper(substr(md5(rand()), 0, 16)),
                'amount' => $order->total_amount,
                'currency' => $order->currency_code,
                'status' => $statuses[array_rand($statuses)],
                'payment_data' => json_encode([
                    'gateway' => 'stripe',
                    'payment_intent' => 'pi_' . strtoupper(substr(md5(rand()), 0, 14)),
                    'status' => 'succeeded',
                ]),
            ]);
        }
    }
}
