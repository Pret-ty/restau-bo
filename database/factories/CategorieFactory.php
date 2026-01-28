<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategorieFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => $this->faker->word(),
            'restaurant_id' => \App\Models\Restaurant::factory(),
        ];
    }
}
