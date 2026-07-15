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

        foreach ($carts as $cart) {
            // 0-3 items per cart
            $itemCount = rand(0, 3);

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
