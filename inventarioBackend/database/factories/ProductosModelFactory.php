<?php

namespace Database\Factories;

use App\Models\proveedorModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductosModel>
 */
class ProductosModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => $this->faker->words(3, true),
            'descripcion' => $this->faker->sentence(),
            'precio' => $this->faker->randomFloat(2, 10, 1000),
            'cantidad_disponible' => $this->faker->numberBetween(0, 100),
            'categoria' => $this->faker->word(),
            'proveedor' => proveedorModel::factory(),
            'codigoProducto' => $this->faker->unique()->ean13(),
            'estado' => $this->faker->boolean(),
        ];
    }
}
