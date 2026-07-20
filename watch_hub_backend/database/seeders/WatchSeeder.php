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

        // ✅ Fixed images for each brand
        $brandImages = [
            'Rolex' => [
                'https://picsum.photos/seed/rolex1/600/600',
                'https://picsum.photos/seed/rolex2/600/600',
                'https://picsum.photos/seed/rolex3/600/600',
            ],
            'Casio' => [
                'https://picsum.photos/seed/casio1/600/600',
                'https://picsum.photos/seed/casio2/600/600',
                'https://picsum.photos/seed/casio3/600/600',
            ],
            'Seiko' => [
                'https://picsum.photos/seed/seiko1/600/600',
                'https://picsum.photos/seed/seiko2/600/600',
                'https://picsum.photos/seed/seiko3/600/600',
            ],
            'Fossil' => [
                'https://picsum.photos/seed/fossil1/600/600',
                'https://picsum.photos/seed/fossil2/600/600',
                'https://picsum.photos/seed/fossil3/600/600',
            ],
            'Titan' => [
                'https://picsum.photos/seed/titan1/600/600',
                'https://picsum.photos/seed/titan2/600/600',
                'https://picsum.photos/seed/titan3/600/600',
            ],
            'Omega' => [
                'https://picsum.photos/seed/omega1/600/600',
                'https://picsum.photos/seed/omega2/600/600',
                'https://picsum.photos/seed/omega3/600/600',
            ],
            'Tag Heuer' => [
                'https://picsum.photos/seed/tag1/600/600',
                'https://picsum.photos/seed/tag2/600/600',
                'https://picsum.photos/seed/tag3/600/600',
            ],
            'Citizen' => [
                'https://picsum.photos/seed/citizen1/600/600',
                'https://picsum.photos/seed/citizen2/600/600',
                'https://picsum.photos/seed/citizen3/600/600',
            ],
            'Timex' => [
                'https://picsum.photos/seed/timex1/600/600',
                'https://picsum.photos/seed/timex2/600/600',
                'https://picsum.photos/seed/timex3/600/600',
            ],
        ];

        foreach ($watches as $watch) {
            $brand = $watch->brand->name;
            $images = $brandImages[$brand] ?? [
                'https://picsum.photos/seed/' . $watch->slug . '1/600/600',
                'https://picsum.photos/seed/' . $watch->slug . '2/600/600',
                'https://picsum.photos/seed/' . $watch->slug . '3/600/600',
            ];

            $imageCount = rand(2, 3);

            for ($i = 0; $i < $imageCount; $i++) {
                WatchImage::create([
                    'watch_id' => $watch->id,
                    'image_url' => $images[$i],
                    'is_primary' => $i === 0,
                    'sort_order' => $i,
                ]);
            }
        }
    }
}
