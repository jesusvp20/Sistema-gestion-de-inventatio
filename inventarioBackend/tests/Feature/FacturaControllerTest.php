<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\facturaModel;
use App\Models\DetalleFacturaModel;
use App\Models\ProductosModel;
use App\Models\ClientesModel;

class FacturaControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function puede_listar_facturas()
    {
        // Crear 5 facturas con sus clientes
        facturaModel::factory()->count(5)->create();

        $response = $this->getJson(route('facturas.listar'));

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(5, 'data.data') // Paginator data is nested
            ->assertJsonStructure([
                'status',
                'data' => [
                    'data' => [
                        '*' => ['id', 'numero_facturas', 'fecha', 'cliente_id', 'total', 'estado', 'cliente']
                    ]
                ]
            ]);
    }

    /** @test */
    public function puede_crear_una_factura_y_actualiza_el_stock()
    {
        $cliente = ClientesModel::factory()->create();
        $producto1 = ProductosModel::factory()->create(['cantidad_disponible' => 50, 'precio' => 10.00]);
        $producto2 = ProductosModel::factory()->create(['cantidad_disponible' => 30, 'precio' => 20.00]);

        $facturaData = [
            'cliente_id' => $cliente->id,
            'detalles' => [
                ['producto_id' => $producto1->IdProducto, 'cantidad' => 5],
                ['producto_id' => $producto2->IdProducto, 'cantidad' => 2],
            ]
        ];

        $response = $this->postJson(route('facturas.crear'), $facturaData);

        $expectedTotal = (5 * 10.00) + (2 * 20.00);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Factura creada exitosamente');

        $this->assertEquals($expectedTotal, $response->json('data.total'));

        // Verificar que la factura y los detalles se crearon en la BD
        $this->assertDatabaseHas('facturas', [
            'cliente_id' => $cliente->id,
            'total' => $expectedTotal
        ]);
        $this->assertDatabaseHas('detallefactura', ['producto_id' => $producto1->IdProducto, 'cantidad' => 5]);
        $this->assertDatabaseHas('detallefactura', ['producto_id' => $producto2->IdProducto, 'cantidad' => 2]);

        // Verificar que el stock se actualizó correctamente
        $this->assertDatabaseHas('producto', ['IdProducto' => $producto1->IdProducto, 'cantidad_disponible' => 45]); // 50 - 5
        $this->assertDatabaseHas('producto', ['IdProducto' => $producto2->IdProducto, 'cantidad_disponible' => 28]); // 30 - 2
    }

    /** @test */
    public function no_puede_crear_una_factura_sin_stock_suficiente()
    {
        $cliente = ClientesModel::factory()->create();
        $producto = ProductosModel::factory()->create(['cantidad_disponible' => 5]);

        $facturaData = [
            'cliente_id' => $cliente->id,
            'detalles' => [
                ['producto_id' => $producto->IdProducto, 'cantidad' => 10], // Intentando vender más de lo disponible
            ]
        ];

        $response = $this->postJson(route('facturas.crear'), $facturaData);

        $response->assertStatus(500) // La transacción lanza una excepción
            ->assertJsonPath('status', 'error')
            ->assertJsonFragment(['message' => 'Error al crear la factura']);

        // Verificar que el stock no cambió y no se creó la factura
        $this->assertDatabaseHas('producto', ['IdProducto' => $producto->IdProducto, 'cantidad_disponible' => 5]);
        $this->assertDatabaseCount('facturas', 0);
        $this->assertDatabaseCount('detallefactura', 0);
    }
}
