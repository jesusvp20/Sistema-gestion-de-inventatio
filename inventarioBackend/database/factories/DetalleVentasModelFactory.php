<?php

namespace Database\Factories;

use App\Models\ProductosModel;
use App\Models\ventasModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DetalleVentasModel>
 */
class DetalleVentasModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $producto = ProductosModel::factory()->create();
        return [
            'id_venta' => ventasModel::factory(),
            'id_producto' => $producto->IdProducto,
            'cantidad' => $this->faker->numberBetween(1, 5),
            'precio' => $producto->precio,
        ];
    }
}
