<?php

namespace Database\Seeders;

use App\Models\AdminActivityLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();

        if (!$admin) return;

        $actions = [
            'login',
            'logout',
            'view_dashboard',
            'update_order_status',
            'add_product',
            'edit_product',
            'delete_product',
            'view_users',
            'toggle_user_status',
            'moderate_review',
            'create_coupon',
            'update_settings',
        ];

        $modelTypes = ['App\Models\Order', 'App\Models\Watch', 'App\Models\User', 'App\Models\Review', 'App\Models\Coupon'];

        for ($i = 0; $i < 20; $i++) {
            $action = $actions[array_rand($actions)];
            $modelType = rand(0, 1) ? $modelTypes[array_rand($modelTypes)] : null;
            $modelId = $modelType ? rand(1, 50) : null;

            AdminActivityLog::create([
                'admin_id' => $admin->id,
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'changes' => json_encode(['field' => 'value', 'timestamp' => now()->toDateTimeString()]),
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'created_at' => now()->subMinutes(rand(1, 60)),
            ]);
        }
    }
}
