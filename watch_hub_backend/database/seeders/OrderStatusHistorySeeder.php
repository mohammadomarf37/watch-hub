<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderStatusHistorySeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::all();
        $admin = User::where('role', 'admin')->first();

        $statusFlow = [
            'pending' => 'processing',
            'processing' => 'shipped',
            'shipped' => 'delivered',
        ];

        foreach ($orders as $order) {
            $statuses = ['pending'];
            $currentStatus = 'pending';

            // Randomly progress through statuses
            $progressLevel = rand(0, 3);

            for ($i = 0; $i < $progressLevel; $i++) {
                if (isset($statusFlow[$currentStatus])) {
                    $nextStatus = $statusFlow[$currentStatus];
                    $statuses[] = $nextStatus;
                    $currentStatus = $nextStatus;
                }
            }

            foreach ($statuses as $index => $status) {
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'status_from' => $index > 0 ? $statuses[$index - 1] : null,
                    'status_to' => $status,
                    'changed_by' => $admin ? $admin->id : 1,
                    'notes' => $index === 0 ? 'Order placed' : 'Order status updated',
                    'created_at' => now()->addMinutes($index * 5),
                ]);
            }
        }
    }
}
