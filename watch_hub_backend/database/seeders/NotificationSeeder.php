<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'customer')->get();

        $types = ['order', 'promotion', 'wishlist', 'support'];
        $titles = [
            'Order Confirmed',
            'Special Offer Just for You!',
            'Item Back in Stock',
            'Your Order Has Been Shipped',
            'Exclusive Discount Available',
        ];
        $messages = [
            'Your order #ORD-12345 has been confirmed.',
            'Get 20% off on luxury watches this weekend!',
            'The watch you wanted is back in stock.',
            'Your order has been shipped and will arrive soon.',
            'Use code SAVE20 for 20% off your next purchase.',
        ];

        foreach ($users as $user) {
            // 0-5 notifications per user
            $notificationCount = rand(0, 5);

            for ($i = 0; $i < $notificationCount; $i++) {
                $isRead = rand(0, 1) === 1;

                Notification::create([
                    'user_id' => $user->id,
                    'type' => $types[array_rand($types)],
                    'title' => $titles[array_rand($titles)],
                    'message' => $messages[array_rand($messages)],
                    'data' => json_encode(['link' => '/notifications']),
                    'read_at' => $isRead ? now()->subHours(rand(1, 48)) : null,
                ]);
            }
        }
    }
}
