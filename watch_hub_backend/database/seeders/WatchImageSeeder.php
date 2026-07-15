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

        foreach ($watches as $watch) {
            $slug = $watch->slug;
            $imageCount = rand(2, 4);

            for ($i = 0; $i < $imageCount; $i++) {
                WatchImage::create([
                    'watch_id' => $watch->id,
                    'image_url' => 'https://picsum.photos/seed/' . $slug . $i . '/600/600',
                    'is_primary' => $i === 0,
                    'sort_order' => $i,
                ]);
            }
        }
    }
}
