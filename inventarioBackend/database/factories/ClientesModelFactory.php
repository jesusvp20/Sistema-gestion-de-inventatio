<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientesModel>
 */
class ClientesModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->name(),
            'identificacion' => $this->faker->unique()->numerify('##########'),
            'email' => $this->faker->unique()->safeEmail(),
            'estado' => $this->faker->boolean(),
            'telefono' => $this->faker->phoneNumber(),
        ];
    }
}
