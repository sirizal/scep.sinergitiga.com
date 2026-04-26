<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        $name = fake()->company();
        $types = ['MAIN', 'TRANSIT', 'DISTRIBUTION'];

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => fake()->randomElement($types),
            'address' => fake()->address(),
            'country_id' => 1,
            'province_id' => fake()->numberBetween(1, 10),
            'district_id' => fake()->numberBetween(1, 20),
            'sub_district_id' => fake()->numberBetween(1, 30),
            'village_id' => fake()->numberBetween(1, 40),
            'postal_code' => fake()->postcode(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
