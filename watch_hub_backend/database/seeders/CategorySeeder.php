<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Analog', 'slug' => Str::slug('Analog')],
            ['name' => 'Digital', 'slug' => Str::slug('Digital')],
            ['name' => 'Smartwatch', 'slug' => Str::slug('Smartwatch')],
            ['name' => 'Chronograph', 'slug' => Str::slug('Chronograph')],
            ['name' => 'Luxury', 'slug' => Str::slug('Luxury')],
            ['name' => 'Sports', 'slug' => Str::slug('Sports')],
            ['name' => 'Dress', 'slug' => Str::slug('Dress')],
            ['name' => 'Diver', 'slug' => Str::slug('Diver')],
        ];

        $parentIds = [];

        foreach ($categories as $category) {
            $created = Category::create([
                'name' => $category['name'],
                'slug' => $category['slug'],
                'parent_id' => null,
                'is_active' => true,
            ]);
            $parentIds[$category['name']] = $created->id;
        }

        // Sub-categories
        $subCategories = [
            ['name' => 'Automatic', 'slug' => Str::slug('Automatic'), 'parent' => 'Analog'],
            ['name' => 'Quartz', 'slug' => Str::slug('Quartz'), 'parent' => 'Analog'],
            ['name' => 'Mechanical', 'slug' => Str::slug('Mechanical'), 'parent' => 'Analog'],
            ['name' => 'LCD', 'slug' => Str::slug('LCD'), 'parent' => 'Digital'],
            ['name' => 'LED', 'slug' => Str::slug('LED'), 'parent' => 'Digital'],
            ['name' => 'Fitness', 'slug' => Str::slug('Fitness'), 'parent' => 'Smartwatch'],
            ['name' => 'GPS', 'slug' => Str::slug('GPS'), 'parent' => 'Smartwatch'],
            ['name' => 'Luxury Sport', 'slug' => Str::slug('Luxury Sport'), 'parent' => 'Luxury'],
            ['name' => 'Classic', 'slug' => Str::slug('Classic'), 'parent' => 'Dress'],
        ];

        foreach ($subCategories as $sub) {
            Category::create([
                'name' => $sub['name'],
                'slug' => $sub['slug'],
                'parent_id' => $parentIds[$sub['parent']],
                'is_active' => true,
            ]);
        }
    }
}
