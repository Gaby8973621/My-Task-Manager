<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TareaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'titulo' => $this->faker->sentence,
            'descripcion' => $this->faker->paragraph,
            'completada' => $this->faker->boolean(30),
        ];
    }
}
