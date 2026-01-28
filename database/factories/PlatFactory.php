<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PlatFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nom' => $this->faker->word(),
            'prix' => $this->faker->randomFloat(2, 5, 50),
            'categorie_id' => \App\Models\Categorie::factory(),
        ];
    }
}
