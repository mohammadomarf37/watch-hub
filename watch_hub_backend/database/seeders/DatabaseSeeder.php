<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            UserSeeder::class,
            CurrencySeeder::class,
            BrandSeeder::class,
            CategorySeeder::class,
            ShippingMethodSeeder::class,
            ShippingRateSeeder::class,
            WatchSeeder::class,
            WatchImageSeeder::class,
            WatchPriceSeeder::class,
            WatchSpecificationSeeder::class,
            WatchVariantSeeder::class,
            ReviewSeeder::class,
            SettingSeeder::class,
            AddressSeeder::class,
            PaymentMethodSeeder::class,
            CouponSeeder::class,
            CouponProductSeeder::class,
            CartSeeder::class,
            CartItemSeeder::class,
            WishlistSeeder::class,
            WishlistItemSeeder::class,
            OrderSeeder::class,
            OrderItemSeeder::class,
            PaymentSeeder::class,
            OrderStatusHistorySeeder::class,
            NotificationSeeder::class,
            CouponUsageSeeder::class,
            AdminActivityLogSeeder::class,
        ]);
    }
}
