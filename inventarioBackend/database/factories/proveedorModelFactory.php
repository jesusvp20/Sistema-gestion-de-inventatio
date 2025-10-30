<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\proveedorModel>
 */
class proveedorModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->company(),
            'direccion' => $this->faker->address(),
            'telefono' => $this->faker->phoneNumber(),
            'estado' => $this->faker->boolean(),
        ];
    }
}
