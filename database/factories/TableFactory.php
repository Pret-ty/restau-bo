<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TableFactory extends Factory
{
    public function definition(): array
    {
        return [
            'numero' => 'T' . fake()->numberBetween(1, 100),
            'restaurant_id' => \App\Models\Restaurant::factory(),
        ];
    }
}
