<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    // Get all settings
    public function index(Request $request)
    {
        $query = Setting::query();

        // Filter by group
        if ($request->has('group')) {
            $query->where('group_name', $request->group);
        }

        // Search by key
        if ($request->has('search')) {
            $query->where('key', 'like', '%' . $request->search . '%');
        }

        $settings = $query->orderBy('group_name')->orderBy('key')->get();

        // Group by group_name
        $grouped = $settings->groupBy('group_name');

        return response()->json([
            'success' => true,
            'data' => $grouped
        ]);
    }

    // Update settings
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'settings' => 'required|array',
            'settings.*.key' => 'required|string|exists:settings,key',
            'settings.*.value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        foreach ($request->settings as $setting) {
            Setting::where('key', $setting['key'])->update([
                'value' => $setting['value']
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully'
        ]);
    }

    // Update single setting
    public function updateSingle(Request $request, $key)
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'value' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $setting->update(['value' => $request->value]);

        return response()->json([
            'success' => true,
            'message' => 'Setting updated successfully',
            'data' => $setting
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

    // Create new setting
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string|max:100|unique:settings,key',
            'value' => 'required',
            'group_name' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $setting = Setting::create([
            'key' => $request->key,
            'value' => $request->value,
            'group_name' => $request->group_name,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Setting created successfully',
            'data' => $setting
        ], 201);
    }

    // Delete setting
    public function destroy($key)
    {
        $setting = Setting::where('key', $key)->first();

        if (!$setting) {
            return response()->json([
                'success' => false,
                'message' => 'Setting not found'
            ], 404);
        }

        $setting->delete();

        return response()->json([
            'success' => true,
            'message' => 'Setting deleted successfully'
        ]);
    }

    // Get all setting groups
    public function getGroups()
    {
        $groups = Setting::select('group_name')
            ->distinct()
            ->orderBy('group_name')
            ->pluck('group_name');

        return response()->json([
            'success' => true,
            'data' => $groups
        ]);
    }

    // Reset settings to default
    public function reset()
    {
        $defaults = [
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

        foreach ($defaults as $default) {
            Setting::updateOrCreate(
                ['key' => $default['key']],
                $default
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Settings reset to defaults successfully'
        ]);
    }

    // Get setting value by key
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
}
