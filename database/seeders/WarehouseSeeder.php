<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'name' => 'Main Warehouse Jakarta',
                'type' => 'MAIN',
                'address' => 'Jl. Industrial Raya No. 123',
                'postal_code' => '13930',
                'latitude' => -6.2088000,
                'longitude' => 106.8200000,
                'is_active' => true,
            ],
            [
                'name' => 'Transit Warehouse Surabaya',
                'type' => 'TRANSIT',
                'address' => 'Jl. Pemuda No. 45',
                'postal_code' => '60241',
                'latitude' => -7.2575000,
                'longitude' => 112.7521000,
                'is_active' => true,
            ],
            [
                'name' => 'Distribution Center Bandung',
                'type' => 'DISTRIBUTION',
                'address' => 'Jl. Asia Afrika No. 78',
                'postal_code' => '40111',
                'latitude' => -6.9148000,
                'longitude' => 107.6099000,
                'is_active' => true,
            ],
        ];

        foreach ($warehouses as $warehouseData) {
            Warehouse::create($warehouseData);
        }
    }
}
