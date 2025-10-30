<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\facturaModel;
use App\Models\ClientesModel;
use App\Models\ProductosModel;
use App\Models\DetalleFacturaModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class DetalleFacturaControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Crear datos de prueba básicos
        $this->cliente = ClientesModel::factory()->create();
        $this->producto = ProductosModel::factory()->create([
            'cantidad_disponible' => 100,
            'precio' => 50.00
        ]);
        $this->factura = facturaModel::factory()->create([
            'cliente_id' => $this->cliente->id
        ]);
    }

    /** @test */
    public function puede_listar_facturas_con_detalles()
    {
        $response = $this->getJson('/api/facturas');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'numero_facturas',
                                'fecha',
                                'fecha_formateada',
                                'total',
                                'estado',
                                'cliente',
                                'detalles'
                            ]
                        ]
                    ]
                ]);
    }

    /** @test */
    public function puede_mostrar_factura_individual()
    {
        $response = $this->getJson("/api/facturas/{$this->factura->id}");
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'id',
                        'numero_facturas',
                        'fecha',
                        'fecha_formateada',
                        'total',
                        'estado',
                        'cliente',
                        'detalles'
                    ]
                ]);
    }

    /** @test */
    public function puede_crear_factura_con_detalles()
    {
        $detalles = [
            [
                'producto_id' => $this->producto->IdProducto,
                'cantidad' => 2
            ]
        ];

        $response = $this->postJson('/api/facturas', [
            'cliente_id' => $this->cliente->id,
            'detalles' => $detalles
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'id',
                        'numero_facturas',
                        'fecha',
                        'total',
                        'detalles'
                    ]
                ]);

        $this->assertDatabaseHas('facturas', [
            'cliente_id' => $this->cliente->id
        ]);

        $this->assertDatabaseHas('detallefactura', [
            'producto_id' => $this->producto->IdProducto,
            'cantidad' => 2
        ]);

        // Verificar que el stock se actualizó
        $this->producto->refresh();
        $this->assertEquals(98, $this->producto->cantidad_disponible);
    }

    /** @test */
    public function valida_stock_insuficiente()
    {
        $detalles = [
            [
                'producto_id' => $this->producto->IdProducto,
                'cantidad' => 101 // Más que el stock disponible
            ]
        ];

        $response = $this->postJson('/api/facturas', [
            'cliente_id' => $this->cliente->id,
            'detalles' => $detalles
        ]);

        $response->assertStatus(500)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'Error al crear la factura',
                    'error' => 'Stock no disponible'
                ]);
    }

    /** @test */
    public function puede_agregar_detalle_a_factura_existente()
    {
        $response = $this->postJson("/api/facturas/{$this->factura->id}/detalles", [
            'factura_id' => $this->factura->id,
            'producto_id' => $this->producto->IdProducto,
            'cantidad' => 2
        ]);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data' => [
                        'id',
                        'factura_id',
                        'producto_id',
                        'cantidad',
                        'precio_unitario',
                        'subtotal',
                        'producto'
                    ]
                ]);

        // Verificar actualización de stock
        $this->producto->refresh();
        $this->assertEquals(98, $this->producto->cantidad_disponible);
    }

    /** @test */
    public function puede_actualizar_detalle()
    {
        // Crear un detalle primero
        $detalle = DetalleFacturaModel::create([
            'factura_id' => $this->factura->id,
            'producto_id' => $this->producto->IdProducto,
            'cantidad' => 2,
            'precio_unitario' => $this->producto->precio,
            'subtotal' => $this->producto->precio * 2
        ]);

        $response = $this->putJson("/api/facturas/detalles/{$detalle->id}", [
            'cantidad' => 3
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data'
                ]);

        $this->assertDatabaseHas('detallefactura', [
            'id' => $detalle->id,
            'cantidad' => 3
        ]);
    }

    /** @test */
    public function puede_eliminar_detalle()
    {
        // Crear un detalle primero
        $detalle = DetalleFacturaModel::create([
            'factura_id' => $this->factura->id,
            'producto_id' => $this->producto->IdProducto,
            'cantidad' => 2,
            'precio_unitario' => $this->producto->precio,
            'subtotal' => $this->producto->precio * 2
        ]);

        $stockInicial = $this->producto->cantidad_disponible;

        $response = $this->deleteJson("/api/facturas/detalles/{$detalle->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'status' => 'success',
                    'message' => 'Detalle eliminado'
                ]);

        $this->assertDatabaseMissing('detallefactura', ['id' => $detalle->id]);

        // Verificar que el stock se restauró
        $this->producto->refresh();
        $this->assertEquals($stockInicial + 2, $this->producto->cantidad_disponible);
    }

    /** @test */
    public function puede_validar_detalles_antes_de_crear()
    {
        $response = $this->postJson('/api/facturas/validar-detalles', [
            'detalles' => [
                [
                    'producto_id' => $this->producto->IdProducto,
                    'cantidad' => 2
                ]
            ]
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'items',
                        'total',
                        'errores'
                    ]
                ]);
    }

    /** @test */
    public function puede_listar_productos_disponibles()
    {
        $response = $this->getJson('/api/facturas/productos/listar');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'data' => [
                            '*' => [
                                'IdProducto',
                                'nombre',
                                'precio',
                                'cantidad_disponible',
                                'codigoProducto'
                            ]
                        ]
                    ]
                ]);
    }

    /** @test */
    public function puede_listar_clientes()
    {
        $response = $this->getJson('/api/facturas/clientes/listar');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'nombre',
                                'email',
                                'telefono'
                            ]
                        ]
                    ]
                ]);
    }
}