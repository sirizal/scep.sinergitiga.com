<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = Warehouse::all();

        if ($warehouses->isEmpty()) {
            return;
        }

        $types = ['STORAGE', 'PICKING', 'RECEIVING', 'SHIPPING'];

        foreach ($warehouses as $warehouse) {
            for ($zone = 1; $zone <= 2; $zone++) {
                for ($aisle = 1; $aisle <= 3; $aisle++) {
                    for ($rack = 1; $rack <= 4; $rack++) {
                        for ($level = 1; $level <= 3; $level++) {
                            $bin = fake()->numberBetween(1, 4);

                            Location::create([
                                'zone' => str_pad($zone, 2, '0', STR_PAD_LEFT),
                                'aisle' => str_pad($aisle, 2, '0', STR_PAD_LEFT),
                                'rack' => str_pad($rack, 2, '0', STR_PAD_LEFT),
                                'level' => str_pad($level, 2, '0', STR_PAD_LEFT),
                                'bin' => (string) $bin,
                                'warehouse_id' => $warehouse->id,
                                'type' => fake()->randomElement($types),
                                'name' => "Zone {$zone} - Aisle {$aisle} Rack {$rack} Level {$level} Bin {$bin}",
                                'max_weight' => fake()->randomFloat(2, 100, 5000),
                                'max_volume' => fake()->randomFloat(2, 1, 50),
                                'is_active' => true,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
