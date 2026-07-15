<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General Settings
            ['key' => 'app_name', 'value' => 'WatchHub', 'group_name' => 'general'],
            ['key' => 'app_description', 'value' => 'Premium Watches Store', 'group_name' => 'general'],
            ['key' => 'app_logo', 'value' => '/images/logo.png', 'group_name' => 'general'],
            ['key' => 'app_favicon', 'value' => '/images/favicon.ico', 'group_name' => 'general'],

            // Currency Settings
            ['key' => 'default_currency', 'value' => 'USD', 'group_name' => 'currency'],
            ['key' => 'currency_symbol_position', 'value' => 'before', 'group_name' => 'currency'],
            ['key' => 'decimal_separator', 'value' => '.', 'group_name' => 'currency'],
            ['key' => 'thousand_separator', 'value' => ',', 'group_name' => 'currency'],
            ['key' => 'decimal_places', 'value' => '2', 'group_name' => 'currency'],

            // Shipping Settings
            ['key' => 'free_shipping_threshold', 'value' => '100', 'group_name' => 'shipping'],
            ['key' => 'default_shipping_method', 'value' => '1', 'group_name' => 'shipping'],
            ['key' => 'processing_days', 'value' => '2', 'group_name' => 'shipping'],

            // Tax Settings
            ['key' => 'tax_enabled', 'value' => 'true', 'group_name' => 'tax'],
            ['key' => 'tax_percentage', 'value' => '10', 'group_name' => 'tax'],
            ['key' => 'tax_inclusive', 'value' => 'false', 'group_name' => 'tax'],

            // Order Settings
            ['key' => 'order_prefix', 'value' => 'ORD-', 'group_name' => 'order'],
            ['key' => 'order_digits', 'value' => '6', 'group_name' => 'order'],
            ['key' => 'order_auto_confirm', 'value' => 'true', 'group_name' => 'order'],
            ['key' => 'order_cancel_days', 'value' => '1', 'group_name' => 'order'],

            // Payment Settings
            ['key' => 'payment_methods', 'value' => '["cash_on_delivery","card"]', 'group_name' => 'payment'],
            ['key' => 'cash_on_delivery_enabled', 'value' => 'true', 'group_name' => 'payment'],
            ['key' => 'card_payment_enabled', 'value' => 'true', 'group_name' => 'payment'],

            // Notification Settings
            ['key' => 'order_notifications', 'value' => 'true', 'group_name' => 'notification'],
            ['key' => 'promotional_notifications', 'value' => 'true', 'group_name' => 'notification'],
            ['key' => 'wishlist_notifications', 'value' => 'true', 'group_name' => 'notification'],

            // SEO Settings
            ['key' => 'meta_title', 'value' => 'WatchHub - Premium Watches Store', 'group_name' => 'seo'],
            ['key' => 'meta_description', 'value' => 'Buy premium watches online at WatchHub', 'group_name' => 'seo'],
            ['key' => 'meta_keywords', 'value' => 'watches, premium watches, luxury watches', 'group_name' => 'seo'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }
    }
}
