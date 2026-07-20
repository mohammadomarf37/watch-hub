<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\WatchVariant;
use Illuminate\Database\Seeder;

class CartItemSeeder extends Seeder
{
    public function run(): void
    {
        $carts = Cart::all();
        $variants = WatchVariant::all();

        // Check if variants exist
        if ($variants->count() == 0) {
            $this->command->info('No variants found, skipping CartItemSeeder');
            return;
        }

        foreach ($carts as $cart) {
            $itemCount = rand(0, min(3, $variants->count()));

            if ($itemCount > 0) {
                $selectedVariants = $variants->random($itemCount);

                foreach ($selectedVariants as $variant) {
                    CartItem::create([
                        'cart_id' => $cart->id,
                        'variant_id' => $variant->id,
                        'quantity' => rand(1, 3),
                    ]);
                }
            }
        }
    }
}
