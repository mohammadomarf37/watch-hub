<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Watch;
use App\Models\WatchPrice;
use Illuminate\Database\Seeder;

class WatchPriceSeeder extends Seeder
{
    public function run(): void
    {
        $watches = Watch::all();
        $currencies = Currency::where('is_default', false)->get();

        foreach ($watches as $watch) {
            foreach ($currencies as $currency) {
                WatchPrice::create([
                    'watch_id' => $watch->id,
                    'currency_id' => $currency->id,
                    'price' => $watch->base_price * $currency->exchange_rate,
                    'discounted_price' => $watch->discounted_price ? $watch->discounted_price * $currency->exchange_rate : null,
                ]);
            }
        }
    }
}
