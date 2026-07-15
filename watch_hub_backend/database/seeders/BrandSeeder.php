<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            ['name' => 'Rolex', 'description' => 'Swiss luxury watch manufacturer', 'is_active' => true],
            ['name' => 'Casio', 'description' => 'Japanese electronics company', 'is_active' => true],
            ['name' => 'Seiko', 'description' => 'Japanese watch company', 'is_active' => true],
            ['name' => 'Fossil', 'description' => 'American fashion brand', 'is_active' => true],
            ['name' => 'Titan', 'description' => 'Indian watch brand', 'is_active' => true],
            ['name' => 'Omega', 'description' => 'Swiss luxury watchmaker', 'is_active' => true],
            ['name' => 'Tag Heuer', 'description' => 'Swiss luxury watchmaker', 'is_active' => true],
            ['name' => 'Citizen', 'description' => 'Japanese watch company', 'is_active' => true],
            ['name' => 'Timex', 'description' => 'American watch company', 'is_active' => true],
            ['name' => 'G-Shock', 'description' => 'Casio sub-brand for tough watches', 'is_active' => true],
        ];

        foreach ($brands as $brand) {
            Brand::create($brand);
        }
    }
}
