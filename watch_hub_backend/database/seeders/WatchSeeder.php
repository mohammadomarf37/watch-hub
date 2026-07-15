<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Watch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WatchSeeder extends Seeder
{
    public function run(): void
    {
        $brands = Brand::all();
        $subCategories = Category::whereNotNull('parent_id')->get();

        $watchesData = [
            [
                'model' => 'Submariner Date',
                'brand' => 'Rolex',
                'category' => 'Luxury',
                'description' => 'Iconic dive watch with ceramic bezel and date function',
                'base_price' => 9500.00,
                'discount_percent' => 0,
                'stock' => 10,
                'is_featured' => true,
            ],
            [
                'model' => 'Datejust 41',
                'brand' => 'Rolex',
                'category' => 'Luxury',
                'description' => 'Classic dress watch with date window',
                'base_price' => 8200.00,
                'discount_percent' => 5,
                'stock' => 8,
                'is_featured' => true,
            ],
            [
                'model' => 'G-Shock GA-2100',
                'brand' => 'Casio',
                'category' => 'Sports',
                'description' => 'Tough analog-digital watch with carbon core guard',
                'base_price' => 150.00,
                'discount_percent' => 10,
                'stock' => 50,
                'is_featured' => false,
            ],
            [
                'model' => 'Pro Trek PRW-3500',
                'brand' => 'Casio',
                'category' => 'Sports',
                'description' => 'Professional outdoor watch with compass and altimeter',
                'base_price' => 450.00,
                'discount_percent' => 0,
                'stock' => 15,
                'is_featured' => false,
            ],
            [
                'model' => 'Presage Cocktail Time',
                'brand' => 'Seiko',
                'category' => 'Dress',
                'description' => 'Elegant dress watch inspired by Japanese cocktails',
                'base_price' => 550.00,
                'discount_percent' => 8,
                'stock' => 12,
                'is_featured' => true,
            ],
            [
                'model' => 'Prospex Diver',
                'brand' => 'Seiko',
                'category' => 'Diver',
                'description' => 'Professional dive watch with ISO certification',
                'base_price' => 750.00,
                'discount_percent' => 0,
                'stock' => 7,
                'is_featured' => false,
            ],
            [
                'model' => 'Grant Chronograph',
                'brand' => 'Fossil',
                'category' => 'Chronograph',
                'description' => 'Classic chronograph with three sub-dials',
                'base_price' => 180.00,
                'discount_percent' => 15,
                'stock' => 20,
                'is_featured' => false,
            ],
            [
                'model' => 'Explorer',
                'brand' => 'Fossil',
                'category' => 'Analog',
                'description' => 'Adventure-inspired analog watch with leather strap',
                'base_price' => 140.00,
                'discount_percent' => 10,
                'stock' => 25,
                'is_featured' => false,
            ],
            [
                'model' => 'Edge Smartwatch',
                'brand' => 'Titan',
                'category' => 'Smartwatch',
                'description' => 'Smartwatch with fitness tracking and AMOLED display',
                'base_price' => 250.00,
                'discount_percent' => 0,
                'stock' => 18,
                'is_featured' => true,
            ],
            [
                'model' => 'Speedmaster Professional',
                'brand' => 'Omega',
                'category' => 'Luxury',
                'description' => 'Moonwatch - the first watch on the moon',
                'base_price' => 6200.00,
                'discount_percent' => 0,
                'stock' => 5,
                'is_featured' => true,
            ],
            [
                'model' => 'Aqua Terra',
                'brand' => 'Omega',
                'category' => 'Dress',
                'description' => 'Elegant sports watch with teak dial pattern',
                'base_price' => 5500.00,
                'discount_percent' => 3,
                'stock' => 6,
                'is_featured' => false,
            ],
            [
                'model' => 'Carrera Chronograph',
                'brand' => 'Tag Heuer',
                'category' => 'Chronograph',
                'description' => 'Racing-inspired chronograph with glassbox design',
                'base_price' => 4800.00,
                'discount_percent' => 5,
                'stock' => 8,
                'is_featured' => false,
            ],
            [
                'model' => 'Eco-Drive Chronograph',
                'brand' => 'Citizen',
                'category' => 'Chronograph',
                'description' => 'Solar-powered chronograph with perpetual calendar',
                'base_price' => 380.00,
                'discount_percent' => 12,
                'stock' => 14,
                'is_featured' => false,
            ],
            [
                'model' => 'Expedition Field Watch',
                'brand' => 'Timex',
                'category' => 'Analog',
                'description' => 'Rugged field watch with indiglo backlight',
                'base_price' => 90.00,
                'discount_percent' => 20,
                'stock' => 30,
                'is_featured' => false,
            ],
            [
                'model' => 'Classic Mini',
                'brand' => 'Fossil',
                'category' => 'Analog',
                'description' => 'Slim and elegant mini watch for everyday wear',
                'base_price' => 120.00,
                'discount_percent' => 10,
                'stock' => 22,
                'is_featured' => false,
            ],
            [
                'model' => 'G-Shock Mudmaster',
                'brand' => 'Casio',
                'category' => 'Sports',
                'description' => 'Extreme mud-resistant watch with compass and thermometer',
                'base_price' => 380.00,
                'discount_percent' => 0,
                'stock' => 10,
                'is_featured' => false,
            ],
            [
                'model' => 'Sea Master Diver',
                'brand' => 'Titan',
                'category' => 'Diver',
                'description' => 'Professional dive watch with helium escape valve',
                'base_price' => 320.00,
                'discount_percent' => 5,
                'stock' => 12,
                'is_featured' => false,
            ],
            [
                'model' => 'Presage Sharp Edged',
                'brand' => 'Seiko',
                'category' => 'Analog',
                'description' => 'Sharp angular design with Japanese aesthetics',
                'base_price' => 680.00,
                'discount_percent' => 0,
                'stock' => 9,
                'is_featured' => false,
            ],
            [
                'model' => 'Astron GPS Solar',
                'brand' => 'Seiko',
                'category' => 'Smartwatch',
                'description' => 'GPS solar smartwatch with automatic time zone adjustment',
                'base_price' => 1200.00,
                'discount_percent' => 5,
                'stock' => 6,
                'is_featured' => false,
            ],
            [
                'model' => 'Classic Digital',
                'brand' => 'Casio',
                'category' => 'Digital',
                'description' => 'Classic retro digital watch',
                'base_price' => 45.00,
                'discount_percent' => 15,
                'stock' => 40,
                'is_featured' => false,
            ],
        ];

        foreach ($watchesData as $watchData) {
            $brand = Brand::where('name', $watchData['brand'])->first();
            $category = $subCategories->where('name', $watchData['category'])->first();

            if (!$brand || !$category) continue;

            $slug = Str::slug($brand->name . ' ' . $watchData['model']);

            Watch::create([
                'brand_id' => $brand->id,
                'category_id' => $category->id,
                'model' => $watchData['model'],
                'slug' => $slug,
                'description' => $watchData['description'],
                'base_price' => $watchData['base_price'],
                'discount_percent' => $watchData['discount_percent'],
                'discounted_price' => $watchData['discount_percent'] > 0 ? $watchData['base_price'] * (1 - $watchData['discount_percent'] / 100) : null,
                'stock' => $watchData['stock'],
                'is_featured' => $watchData['is_featured'],
                'is_active' => true,
            ]);
        }
    }
}
