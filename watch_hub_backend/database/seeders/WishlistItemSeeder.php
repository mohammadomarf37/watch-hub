<?php

namespace Database\Seeders;

use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Models\WatchVariant;
use Illuminate\Database\Seeder;

class WishlistItemSeeder extends Seeder
{
    public function run(): void
    {
        $wishlists = Wishlist::all();
        $variants = WatchVariant::all();

        foreach ($wishlists as $wishlist) {
            // 0-5 items per wishlist
            $itemCount = rand(0, 5);

            if ($itemCount > 0) {
                $selectedVariants = $variants->random($itemCount);

                foreach ($selectedVariants as $variant) {
                    WishlistItem::create([
                        'wishlist_id' => $wishlist->id,
                        'variant_id' => $variant->id,
                    ]);
                }
            }
        }
    }
}
