<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Watch;
use App\Models\WatchVariant;
use Illuminate\Database\Seeder;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::all();
        $watches = Watch::all();

        // Check if watches exist
        if ($watches->count() == 0) {
            $this->command->info('No watches found, skipping OrderItemSeeder');
            return;
        }

        foreach ($orders as $order) {
            $itemCount = rand(1, min(3, $watches->count()));
            $selectedWatches = $watches->random($itemCount);

            foreach ($selectedWatches as $watch) {
                $variant = WatchVariant::where('watch_id', $watch->id)->first();

                if (!$variant) {
                    continue;
                }

                $quantity = rand(1, 2);
                $price = $watch->discounted_price ?? $watch->base_price;
                $discount = $watch->discount_percent > 0 ? $price * ($watch->discount_percent / 100) : 0;
                $total = ($price - $discount) * $quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'variant_id' => $variant->id,
                    'watch_id' => $watch->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'discount_applied' => $discount * $quantity,
                    'total' => $total,
                ]);
            }
        }
    }
}
