<?php

namespace Database\Seeders;

use App\Models\Watch;
use App\Models\WatchSpecification;
use Illuminate\Database\Seeder;

class WatchSpecificationSeeder extends Seeder
{
    public function run(): void
    {
        $watches = Watch::all();

        $specsMap = [
            'Rolex Submariner Date' => [
                ['spec_key' => 'Case Material', 'spec_value' => '904L Steel'],
                ['spec_key' => 'Strap Material', 'spec_value' => 'Oystersteel'],
                ['spec_key' => 'Movement', 'spec_value' => 'Automatic'],
                ['spec_key' => 'Water Resistance', 'spec_value' => '300m'],
                ['spec_key' => 'Warranty', 'spec_value' => '5 Years'],
                ['spec_key' => 'Dial Color', 'spec_value' => 'Black'],
            ],
            'Rolex Datejust 41' => [
                ['spec_key' => 'Case Material', 'spec_value' => '904L Steel'],
                ['spec_key' => 'Strap Material', 'spec_value' => 'Jubilee'],
                ['spec_key' => 'Movement', 'spec_value' => 'Automatic'],
                ['spec_key' => 'Water Resistance', 'spec_value' => '100m'],
                ['spec_key' => 'Warranty', 'spec_value' => '5 Years'],
                ['spec_key' => 'Dial Color', 'spec_value' => 'White'],
            ],
            'Casio G-Shock GA-2100' => [
                ['spec_key' => 'Case Material', 'spec_value' => 'Carbon'],
                ['spec_key' => 'Strap Material', 'spec_value' => 'Resin'],
                ['spec_key' => 'Movement', 'spec_value' => 'Quartz'],
                ['spec_key' => 'Water Resistance', 'spec_value' => '200m'],
                ['spec_key' => 'Warranty', 'spec_value' => '2 Years'],
                ['spec_key' => 'Dial Color', 'spec_value' => 'Black'],
            ],
            'Casio Pro Trek PRW-3500' => [
                ['spec_key' => 'Case Material', 'spec_value' => 'Resin'],
                ['spec_key' => 'Strap Material', 'spec_value' => 'Resin'],
                ['spec_key' => 'Movement', 'spec_value' => 'Quartz'],
                ['spec_key' => 'Water Resistance', 'spec_value' => '200m'],
                ['spec_key' => 'Warranty', 'spec_value' => '2 Years'],
                ['spec_key' => 'Dial Color', 'spec_value' => 'Black'],
            ],
            'Seiko Presage Cocktail Time' => [
                ['spec_key' => 'Case Material', 'spec_value' => 'Stainless Steel'],
                ['spec_key' => 'Strap Material', 'spec_value' => 'Calf Leather'],
                ['spec_key' => 'Movement', 'spec_value' => 'Automatic'],
                ['spec_key' => 'Water Resistance', 'spec_value' => '50m'],
                ['spec_key' => 'Warranty', 'spec_value' => '3 Years'],
                ['spec_key' => 'Dial Color', 'spec_value' => 'Blue'],
            ],
            'Seiko Prospex Diver' => [
                ['spec_key' => 'Case Material', 'spec_value' => 'Stainless Steel'],
                ['spec_key' => 'Strap Material', 'spec_value' => 'Stainless Steel'],
                ['spec_key' => 'Movement', 'spec_value' => 'Automatic'],
                ['spec_key' => 'Water Resistance', 'spec_value' => '200m'],
                ['spec_key' => 'Warranty', 'spec_value' => '3 Years'],
                ['spec_key' => 'Dial Color', 'spec_value' => 'Black'],
            ],
            'Fossil Grant Chronograph' => [
                ['spec_key' => 'Case Material', 'spec_value' => 'Stainless Steel'],
                ['spec_key' => 'Strap Material', 'spec_value' => 'Leather'],
                ['spec_key' => 'Movement', 'spec_value' => 'Quartz'],
                ['spec_key' => 'Water Resistance', 'spec_value' => '50m'],
                ['spec_key' => 'Warranty', 'spec_value' => '2 Years'],
                ['spec_key' => 'Dial Color', 'spec_value' => 'Brown'],
            ],
            'Fossil Explorer' => [
                ['spec_key' => 'Case Material', 'spec_value' => 'Stainless Steel'],
                ['spec_key' => 'Strap Material', 'spec_value' => 'Leather'],
                ['spec_key' => 'Movement', 'spec_value' => 'Quartz'],
                ['spec_key' => 'Water Resistance', 'spec_value' => '100m'],
                ['spec_key' => 'Warranty', 'spec_value' => '2 Years'],
                ['spec_key' => 'Dial Color', 'spec_value' => 'Green'],
            ],
            'Titan Edge Smartwatch' => [
                ['spec_key' => 'Case Material', 'spec_value' => 'Aluminum'],
                ['spec_key' => 'Strap Material', 'spec_value' => 'Silicone'],
                ['spec_key' => 'Movement', 'spec_value' => 'Digital'],
                ['spec_key' => 'Water Resistance', 'spec_value' => '50m'],
                ['spec_key' => 'Warranty', 'spec_value' => '1 Year'],
                ['spec_key' => 'Display', 'spec_value' => 'AMOLED'],
            ],
        ];

        foreach ($watches as $watch) {
            $key = $watch->brand->name . ' ' . $watch->model;

            if (isset($specsMap[$key])) {
                foreach ($specsMap[$key] as $spec) {
                    WatchSpecification::create([
                        'watch_id' => $watch->id,
                        'spec_key' => $spec['spec_key'],
                        'spec_value' => $spec['spec_value'],
                    ]);
                }
            } else {
                // Default specs for other watches
                $defaultSpecs = [
                    ['spec_key' => 'Case Material', 'spec_value' => 'Stainless Steel'],
                    ['spec_key' => 'Strap Material', 'spec_value' => 'Leather'],
                    ['spec_key' => 'Movement', 'spec_value' => 'Automatic'],
                    ['spec_key' => 'Water Resistance', 'spec_value' => '100m'],
                    ['spec_key' => 'Warranty', 'spec_value' => '2 Years'],
                    ['spec_key' => 'Dial Color', 'spec_value' => 'Black'],
                ];

                foreach ($defaultSpecs as $spec) {
                    WatchSpecification::create([
                        'watch_id' => $watch->id,
                        'spec_key' => $spec['spec_key'],
                        'spec_value' => $spec['spec_value'],
                    ]);
                }
            }
        }
    }
}
