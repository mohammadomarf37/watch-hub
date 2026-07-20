<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    // Get all public settings
    public function getPublicSettings()
    {
        $settings = Setting::whereIn('group_name', [
            'general',
            'currency',
            'shipping',
            'tax',
            'payment',
            'seo'
        ])->get();

        $formatted = [];
        foreach ($settings as $setting) {
            $formatted[$setting->key] = $setting->value;
        }

        return response()->json([
            'success' => true,
            'data' => $formatted
        ]);
    }

    // Get settings by group
    public function getByGroup($group)
    {
        $validGroups = ['general', 'currency', 'shipping', 'tax', 'order', 'payment', 'notification', 'seo'];

        if (!in_array($group, $validGroups)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid group name'
            ], 400);
        }

        $settings = Setting::where('group_name', $group)->get();

        $formatted = [];
        foreach ($settings as $setting) {
            $formatted[$setting->key] = $setting->value;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'group' => $group,
                'settings' => $formatted
            ]
        ]);
    }

    // Get single setting by key
    public function getByKey($key)
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'key' => $setting->key,
                'value' => $setting->value,
                'group' => $setting->group_name
            ]
        ]);
    }

    // Get app settings (for app initialization)
    public function appSettings()
    {
        $settings = Setting::whereIn('key', [
            'app_name',
            'app_description',
            'app_logo',
            'default_currency',
            'currency_symbol_position',
            'decimal_places',
            'free_shipping_threshold',
            'tax_percentage',
            'meta_title',
            'meta_description'
        ])->get();

        $formatted = [];
        foreach ($settings as $setting) {
            $formatted[$setting->key] = $setting->value;
        }

        // Add default values if not set
        $defaults = [
            'app_name' => 'WatchHub',
            'app_description' => 'Premium Watches Store',
            'app_logo' => '/images/logo.png',
            'default_currency' => 'USD',
            'currency_symbol_position' => 'before',
            'decimal_places' => '2',
            'free_shipping_threshold' => '100',
            'tax_percentage' => '10',
            'meta_title' => 'WatchHub - Premium Watches Store',
            'meta_description' => 'Buy premium watches online at WatchHub'
        ];

        foreach ($defaults as $key => $value) {
            if (!isset($formatted[$key])) {
                $formatted[$key] = $value;
            }
        }

        return response()->json([
            'success' => true,
            'data' => $formatted
        ]);
    }

    // Get currency settings
    public function currencySettings()
    {
        $settings = Setting::where('group_name', 'currency')->get();

        $formatted = [];
        foreach ($settings as $setting) {
            $formatted[$setting->key] = $setting->value;
        }

        return response()->json([
            'success' => true,
            'data' => $formatted
        ]);
    }

    // Get shipping settings
    public function shippingSettings()
    {
        $settings = Setting::where('group_name', 'shipping')->get();

        $formatted = [];
        foreach ($settings as $setting) {
            $formatted[$setting->key] = $setting->value;
        }

        return response()->json([
            'success' => true,
            'data' => $formatted
        ]);
    }

    // Get tax settings
    public function taxSettings()
    {
        $settings = Setting::where('group_name', 'tax')->get();

        $formatted = [];
        foreach ($settings as $setting) {
            $formatted[$setting->key] = $setting->value;
        }

        return response()->json([
            'success' => true,
            'data' => $formatted
        ]);
    }

    // Get SEO settings
    public function seoSettings()
    {
        $settings = Setting::where('group_name', 'seo')->get();

        $formatted = [];
        foreach ($settings as $setting) {
            $formatted[$setting->key] = $setting->value;
        }

        return response()->json([
            'success' => true,
            'data' => $formatted
        ]);
    }
}
