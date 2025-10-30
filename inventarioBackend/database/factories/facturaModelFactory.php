<?php

namespace Database\Factories;

use App\Models\facturaModel;
use App\Models\ClientesModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class facturaModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = facturaModel::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'numero_facturas' => $this->faker->unique()->ean13(),
            'fecha' => $this->faker->dateTimeThisYear(),
            'cliente_id' => ClientesModel::factory(),
            'total' => $this->faker->randomFloat(2, 100, 1000),
            'estado' => $this->faker->boolean(90),
        ];
    }
}
