<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class LocationFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        return [
            'code' => Location::generateCode(new Location([
                'zone' => str_pad(fake()->numberBetween(1, 9), 2, '0', STR_PAD_LEFT),
                'aisle' => str_pad(fake()->numberBetween(1, 9), 2, '0', STR_PAD_LEFT),
                'rack' => str_pad(fake()->numberBetween(1, 9), 2, '0', STR_PAD_LEFT),
                'level' => str_pad(fake()->numberBetween(1, 9), 2, '0', STR_PAD_LEFT),
                'bin' => fake()->numberBetween(1, 9),
                'warehouse_id' => null,
            ])),
            'zone' => str_pad(fake()->numberBetween(1, 9), 2, '0', STR_PAD_LEFT),
            'aisle' => str_pad(fake()->numberBetween(1, 9), 2, '0', STR_PAD_LEFT),
            'rack' => str_pad(fake()->numberBetween(1, 9), 2, '0', STR_PAD_LEFT),
            'level' => str_pad(fake()->numberBetween(1, 9), 2, '0', STR_PAD_LEFT),
            'bin' => (string) fake()->numberBetween(1, 9),
            'warehouse_id' => null,
            'type' => fake()->randomElement(['STORAGE', 'PICKING', 'RECEIVING', 'SHIPPING']),
            'name' => fake()->words(3, true),
            'max_weight' => fake()->randomFloat(2, 100, 10000),
            'max_volume' => fake()->randomFloat(2, 1, 100),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function forWarehouse(Warehouse $warehouse): static
    {
        return $this->state(fn (array $attributes) => [
            'warehouse_id' => $warehouse->id,
        ]);
    }
}
