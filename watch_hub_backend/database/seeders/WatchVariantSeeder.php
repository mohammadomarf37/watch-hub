<?php

namespace Database\Seeders;

use App\Models\Watch;
use App\Models\WatchVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WatchVariantSeeder extends Seeder
{
    public function run(): void
    {
        $watches = Watch::all();

        $colorMap = [
            'Rolex Submariner Date' => ['Black', 'Blue'],
            'Rolex Datejust 41' => ['White', 'Silver'],
            'Casio G-Shock GA-2100' => ['Black', 'Red', 'Blue'],
            'Casio Pro Trek PRW-3500' => ['Black', 'Green'],
            'Seiko Presage Cocktail Time' => ['Blue', 'White', 'Green'],
            'Seiko Prospex Diver' => ['Black', 'Blue'],
            'Fossil Grant Chronograph' => ['Brown', 'Black', 'Silver'],
            'Fossil Explorer' => ['Green', 'Brown', 'Black'],
            'Titan Edge Smartwatch' => ['Black', 'Silver', 'Rose Gold'],
            'Omega Speedmaster Professional' => ['Black'],
            'Omega Aqua Terra' => ['Blue', 'White', 'Green'],
            'Tag Heuer Carrera Chronograph' => ['Blue', 'Black', 'Brown'],
            'Citizen Eco-Drive Chronograph' => ['Blue', 'Silver', 'Black'],
            'Timex Expedition Field Watch' => ['Green', 'Black', 'Tan'],
            'Fossil Classic Mini' => ['White', 'Rose Gold', 'Silver'],
            'Casio G-Shock Mudmaster' => ['Black', 'Olive'],
            'Titan Sea Master Diver' => ['Black', 'Blue'],
            'Seiko Presage Sharp Edged' => ['Blue', 'White', 'Black'],
            'Seiko Astron GPS Solar' => ['Black', 'Silver', 'Gold'],
            'Casio Classic Digital' => ['Black', 'Silver'],
        ];

        $sizeMap = [
            'Rolex Submariner Date' => ['40mm', '41mm'],
            'Rolex Datejust 41' => ['41mm'],
            'Casio G-Shock GA-2100' => ['Standard'],
            'Casio Pro Trek PRW-3500' => ['Standard'],
            'Seiko Presage Cocktail Time' => ['40mm'],
            'Seiko Prospex Diver' => ['42mm', '44mm'],
            'Fossil Grant Chronograph' => ['42mm'],
            'Fossil Explorer' => ['44mm'],
            'Titan Edge Smartwatch' => ['42mm', '46mm'],
            'Omega Speedmaster Professional' => ['42mm'],
            'Omega Aqua Terra' => ['38mm', '41mm'],
            'Tag Heuer Carrera Chronograph' => ['42mm'],
            'Citizen Eco-Drive Chronograph' => ['43mm'],
            'Timex Expedition Field Watch' => ['40mm'],
            'Fossil Classic Mini' => ['32mm'],
            'Casio G-Shock Mudmaster' => ['Standard'],
            'Titan Sea Master Diver' => ['44mm'],
            'Seiko Presage Sharp Edged' => ['40mm'],
            'Seiko Astron GPS Solar' => ['42mm'],
            'Casio Classic Digital' => ['Standard'],
        ];

        foreach ($watches as $watch) {
            $key = $watch->brand->name . ' ' . $watch->model;

            $colors = $colorMap[$key] ?? ['Black', 'Silver'];
            $sizes = $sizeMap[$key] ?? ['Standard'];

            foreach ($colors as $color) {
                foreach ($sizes as $size) {
                    $colorHex = '#' . substr(md5($color), 0, 6);

                    WatchVariant::create([
                        'watch_id' => $watch->id,
                        'color' => $color,
                        'color_hex' => $colorHex,
                        'size' => $size,
                        'stock' => rand(5, 20),
                        'additional_price' => rand(0, 50),
                        'sku' => strtoupper(Str::random(8)),
                    ]);
                }
            }
        }
    }
}
