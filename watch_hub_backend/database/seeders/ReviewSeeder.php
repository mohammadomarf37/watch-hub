<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use App\Models\Watch;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::where('role', 'customer')->get();
        $watches = Watch::all();

        $reviewTitles = [
            'Excellent watch!',
            'Great value for money',
            'Beautiful design',
            'Highly recommended',
            'Good quality',
            'Perfect gift',
            'Amazing craftsmanship',
            'Worth every penny',
            'Love it!',
            'Disappointed with quality',
        ];

        $comments = [
            'The watch looks even better in person. Very satisfied with the purchase.',
            'Great quality for the price. The strap is comfortable and the dial is beautiful.',
            'Excellent build quality. Will definitely buy from this brand again.',
            'The delivery was fast and the watch is exactly as described.',
            'Amazing watch! Received many compliments. The craftsmanship is top-notch.',
            'Good watch but the strap could be better. Overall satisfied.',
            'Absolutely love this watch. It\'s become my daily wear.',
            'The watch is beautiful but the packaging could be improved.',
            'Really happy with the purchase. The watch exceeded my expectations.',
            'Disappointed with the quality. The watch stopped working after a week.',
        ];

        $statuses = ['pending', 'approved', 'approved', 'approved', 'approved'];

        foreach ($watches as $watch) {
            $numberOfReviews = rand(2, 5);
            $reviewUsers = $users->random(min($numberOfReviews, $users->count()));

            $totalRating = 0;

            foreach ($reviewUsers as $user) {
                $rating = rand(3, 5);
                $totalRating += $rating;

                Review::create([
                    'user_id' => $user->id,
                    'watch_id' => $watch->id,
                    'order_id' => null,
                    'rating' => $rating,
                    'title' => $reviewTitles[array_rand($reviewTitles)],
                    'comment' => $comments[array_rand($comments)],
                    'is_verified_purchase' => (bool) rand(0, 1),
                    'status' => $statuses[array_rand($statuses)],
                ]);
            }

            // Update watch rating
            if ($numberOfReviews > 0) {
                $watch->rating = round($totalRating / $numberOfReviews, 2);
                $watch->rating_count = $numberOfReviews;
                $watch->save();
            }
        }
    }
}
