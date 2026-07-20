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

        // Check if variants exist
        if ($variants->count() == 0) {
            $this->command->info('No variants found, skipping WishlistItemSeeder');
            return;
        }

        foreach ($wishlists as $wishlist) {
            $itemCount = rand(0, min(5, $variants->count()));

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
