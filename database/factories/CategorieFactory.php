<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategorieFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => fake()->word(),
            'restaurant_id' => \App\Models\Restaurant::factory(),
        ];
    }
}
