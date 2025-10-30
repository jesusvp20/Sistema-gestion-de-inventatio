<?php

namespace Database\Factories;

use App\Models\ClientesModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ventasModel>
 */
class VentasModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id_cliente' => ClientesModel::factory(),
            'total' => $this->faker->randomFloat(2, 50, 2000),
            'fecha_venta' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
