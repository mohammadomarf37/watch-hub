<?php

namespace Database\Seeders;

use App\Models\Watch;
use App\Models\WatchImage;
use Illuminate\Database\Seeder;

class WatchImageSeeder extends Seeder
{
    public function run(): void
    {
        $watches = Watch::all();

        // ✅ Real watch images from Unsplash
        $watchImages = [
            'Rolex Submariner' => [
                'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?w=600',  // Rolex
                'https://images.unsplash.com/photo-1585123334904-845d60e6b2b2?w=600',
                'https://images.unsplash.com/photo-1547996160-81dfa63595aa?w=600',
            ],
            'Rolex Datejust' => [
                'https://images.unsplash.com/photo-1587836374828-4dbafa94cf0e?w=600',
                'https://images.unsplash.com/photo-1552149779-0a8d328bb851?w=600',
            ],
            'Casio G-Shock' => [
                'https://images.unsplash.com/photo-1542496658-e33a6d0d50f6?w=600',  // G-Shock
                'https://images.unsplash.com/photo-1533139502658-0198f920d8e8?w=600',
            ],
            'Casio Digital' => [
                'https://images.unsplash.com/photo-1586495777744-4413f21062fa?w=600',
                'https://images.unsplash.com/photo-1533139502658-0198f920d8e8?w=600',
            ],
            'Seiko Presage' => [
                'https://images.unsplash.com/photo-1539874754764-5a96559165b0?w=600',  // Dress watch
                'https://images.unsplash.com/photo-1509048191080-d2984bad6ae5?w=600',
            ],
            'Seiko Diver' => [
                'https://images.unsplash.com/photo-1585123334904-845d60e6b2b2?w=600',
                'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?w=600',
            ],
            'Fossil Grant' => [
                'https://images.unsplash.com/photo-1559582858-27b0ed372d23?w=600',  // Chronograph
                'https://images.unsplash.com/photo-1587836374828-4dbafa94cf0e?w=600',
            ],
            'Titan Smartwatch' => [
                'https://images.unsplash.com/photo-1579586337278-3befd40fd17a?w=600',  // Smartwatch
                'https://images.unsplash.com/photo-1508685096489-7aacd43bd3b1?w=600',
            ],
            'Omega Speedmaster' => [
                'https://images.unsplash.com/photo-1539874754764-5a96559165b0?w=600',
                'https://images.unsplash.com/photo-1585123334904-845d60e6b2b2?w=600',
            ],
            'Omega Aqua Terra' => [
                'https://images.unsplash.com/photo-1587836374828-4dbafa94cf0e?w=600',
                'https://images.unsplash.com/photo-1559582858-27b0ed372d23?w=600',
            ],
        ];

        foreach ($watches as $watch) {
            // Find matching images based on brand and model
            $key = $watch->brand->name . ' ' . explode(' ', $watch->model)[0];
            $images = [];

            foreach ($watchImages as $searchKey => $imgArray) {
                if (str_contains($searchKey, $watch->brand->name)) {
                    $images = $imgArray;
                    break;
                }
            }

            // Fallback images if no match found
            if (empty($images)) {
                $images = [
                    'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?w=600',
                    'https://images.unsplash.com/photo-1585123334904-845d60e6b2b2?w=600',
                    'https://images.unsplash.com/photo-1547996160-81dfa63595aa?w=600',
                ];
            }

            // Shuffle and limit to 2-3 images
            shuffle($images);
            $imageCount = rand(2, min(3, count($images)));

            for ($i = 0; $i < $imageCount; $i++) {
                WatchImage::create([
                    'watch_id' => $watch->id,
                    'image_url' => $images[$i],
                    'is_primary' => $i === 0,
                    'sort_order' => $i,
                ]);
            }

            $this->command->info('Added ' . $imageCount . ' images for ' . $watch->brand->name . ' ' . $watch->model);
        }
    }
}
