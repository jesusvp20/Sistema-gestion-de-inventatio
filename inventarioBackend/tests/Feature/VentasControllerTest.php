<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\ventasModel;
use App\Models\DetalleVentasModel;
use App\Models\ProductosModel;
use App\Models\ClientesModel;

class VentasControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function puede_listar_ventas()
    {
        ventasModel::factory()->count(3)->create()->each(function ($venta) {
            DetalleVentasModel::factory()->count(2)->create(['id_venta' => $venta->id_ventas]);
        });

        $response = $this->getJson('/api/ventas');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.data')
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => ['id_ventas', 'id_cliente', 'total', 'cliente', 'detalles']
                    ]
                ]
            ]);
    }

    /** @test */
    public function puede_crear_una_venta()
    {
        $cliente = ClientesModel::factory()->create();
        $producto1 = ProductosModel::factory()->create(['cantidad_disponible' => 20]);
        $producto2 = ProductosModel::factory()->create(['cantidad_disponible' => 15]);

        $ventaData = [
            'id_cliente' => $cliente->id,
            'detalles' => [
                ['id_producto' => $producto1->IdProducto, 'cantidad' => 5],
                ['id_producto' => $producto2->IdProducto, 'cantidad' => 10],
            ]
        ];

        $response = $this->postJson('/api/ventas', $ventaData);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Venta creada exitosamente');

        $this->assertDatabaseHas('ventas', ['id_cliente' => $cliente->id]);
        $this->assertDatabaseHas('detalle_ventas', ['id_producto' => $producto1->IdProducto, 'cantidad' => 5]);
        $this->assertDatabaseHas('producto', ['IdProducto' => $producto1->IdProducto, 'cantidad_disponible' => 15]);
        $this->assertDatabaseHas('producto', ['IdProducto' => $producto2->IdProducto, 'cantidad_disponible' => 5]);
    }

    /** @test */
    public function no_puede_crear_una_venta_sin_stock()
    {
        $cliente = ClientesModel::factory()->create();
        $producto = ProductosModel::factory()->create(['cantidad_disponible' => 5]);

        $ventaData = [
            'id_cliente' => $cliente->id,
            'detalles' => [
                ['id_producto' => $producto->IdProducto, 'cantidad' => 10],
            ]
        ];

        $response = $this->postJson('/api/ventas', $ventaData);

        $response->assertStatus(500) // O el código de error que devuelva la transacción
            ->assertJsonPath('status', 'error');
        
        $this->assertDatabaseHas('producto', ['IdProducto' => $producto->IdProducto, 'cantidad_disponible' => 5]);
    }

    /** @test */
    public function puede_actualizar_una_venta()
    {
        $cliente = ClientesModel::factory()->create();
        $producto1 = ProductosModel::factory()->create(['cantidad_disponible' => 20]);
        $producto2 = ProductosModel::factory()->create(['cantidad_disponible' => 10]);

        $venta = ventasModel::factory()->create(['id_cliente' => $cliente->id]);
        DetalleVentasModel::factory()->create(['id_venta' => $venta->id_ventas, 'id_producto' => $producto1->IdProducto, 'cantidad' => 5]);
        $producto1->refresh(); // Refresh to get updated stock

        $updateData = [
            'id_cliente' => $cliente->id,
            'detalles' => [
                ['id_producto' => $producto2->IdProducto, 'cantidad' => 3],
            ]
        ];

        $response = $this->putJson("/api/ventas/{$venta->id_ventas}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // Stock for producto1 should be restored
        $this->assertDatabaseHas('producto', ['IdProducto' => $producto1->IdProducto, 'cantidad_disponible' => $producto1->cantidad_disponible + 5]);
        // Stock for producto2 should be reduced
        $this->assertDatabaseHas('producto', ['IdProducto' => $producto2->IdProducto, 'cantidad_disponible' => 7]);
    }

    /** @test */
    public function puede_eliminar_una_venta_y_restaurar_stock()
    {
        $producto = ProductosModel::factory()->create(['cantidad_disponible' => 10]);
        $venta = ventasModel::factory()->create();
        DetalleVentasModel::factory()->create(['id_venta' => $venta->id_ventas, 'id_producto' => $producto->IdProducto, 'cantidad' => 5]);

        $producto->refresh();

        // Manually update the stock for the test setup
        $producto->cantidad_disponible = 5;
        $producto->save();

        $this->assertEquals(5, $producto->cantidad_disponible);

        $response = $this->deleteJson("/api/ventas/{$venta->id_ventas}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('ventas', ['id_ventas' => $venta->id_ventas]);
        $this->assertDatabaseHas('producto', ['IdProducto' => $producto->IdProducto, 'cantidad_disponible' => 10]);
    }

    /** @test */
    public function puede_listar_historial_de_ventas()
    {
        ventasModel::factory()->count(3)->create()->each(function ($venta) {
            DetalleVentasModel::factory()->count(2)->create(['id_venta' => $venta->id_ventas]);
        });

        $response = $this->getJson('/api/ventas/historial');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data.data')
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => ['id_ventas', 'id_cliente', 'total', 'cliente', 'detalles']
                    ]
                ]
            ]);
    }
}
