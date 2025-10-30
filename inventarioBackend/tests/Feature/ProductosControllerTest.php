<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\ProductosModel;
use App\Models\proveedorModel;

class ProductosControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function puede_listar_productos()
    {
        ProductosModel::factory()->count(3)->create();

        // Assuming apiResource for productos, using named routes is better practice.
        $response = $this->getJson(route('productos.index'));

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['IdProducto', 'nombre', 'descripcion', 'precio', 'cantidad_disponible', 'categoria', 'proveedor', 'codigoProducto', 'estado']
                ]
            ]);
    }

    /** @test */
    public function puede_crear_un_producto()
    {
        $proveedor = proveedorModel::factory()->create();
        $productoData = [
            'nombre' => 'Producto de Prueba',
            'descripcion' => 'Esta es una descripcion de prueba',
            'precio' => 150.50,
            'cantidad_disponible' => 10,
            'categoria' => 'Categoria de prueba',
            'proveedor' => $proveedor->id,
            'codigoProducto' => 'PROD-TEST-123',
            'estado' => true,
        ];

        $response = $this->postJson(route('productos.store'), $productoData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Producto creado exitosamente',
            ])
            ->assertJsonPath('data.nombre', 'Producto de Prueba');

        $this->assertDatabaseHas('producto', ['codigoProducto' => 'PROD-TEST-123']);
    }

    /** @test */
    public function puede_buscar_un_producto_por_id()
    {
        $producto = ProductosModel::factory()->create();

        $response = $this->getJson(route('productos.show', $producto->IdProducto));

        $response->assertStatus(200)
            ->assertJsonPath('data.IdProducto', $producto->IdProducto);
    }

    /** @test */
    public function puede_actualizar_un_producto()
    {
        $producto = ProductosModel::factory()->create();

        $updateData = ['nombre' => 'Nombre Actualizado'];

        $response = $this->putJson(route('productos.update', $producto->IdProducto), $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Producto actualizado exitosamente',
            ])
            ->assertJsonPath('data.nombre', 'Nombre Actualizado');

        $this->assertDatabaseHas('producto', ['IdProducto' => $producto->IdProducto, 'nombre' => 'Nombre Actualizado']);
    }

    /** @test */
    public function puede_eliminar_un_producto()
    {
        $producto = ProductosModel::factory()->create();

        $response = $this->deleteJson(route('productos.destroy', $producto->IdProducto));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'El producto se ha eliminado correctamente',
            ]);

        $this->assertDatabaseMissing('producto', ['IdProducto' => $producto->IdProducto]);
    }

    /** @test */
    public function puede_cambiar_el_estado_de_un_producto()
    {
        $producto = ProductosModel::factory()->create(['estado' => true]);

        // Assuming a route like 'productos.cambiar-estado'
        $response = $this->patchJson(route('productos.cambiar-estado', $producto->IdProducto));

        $response->assertStatus(200)
            ->assertJsonPath('data.estado', false);

        $this->assertDatabaseHas('producto', ['IdProducto' => $producto->IdProducto, 'estado' => false]);
    }

    /** @test */
    public function puede_buscar_productos_por_nombre()
    {
        ProductosModel::factory()->create(['nombre' => 'Producto Buscado']);
        ProductosModel::factory()->create(['nombre' => 'Otro Producto']);

        // Assuming a route like 'productos.buscar'
        $response = $this->getJson(route('productos.buscar', ['nombre' => 'Buscado']));

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre', 'Producto Buscado');
    }

    /** @test */
    public function puede_listar_productos_activos()
    {
        ProductosModel::factory()->create(['estado' => true]);
        ProductosModel::factory()->create(['estado' => false]);
        ProductosModel::factory()->create(['estado' => true]);

        // Assuming a route like 'productos.activos'
        $response = $this->getJson(route('productos.activos'));

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function puede_ordenar_productos_por_precio()
    {
        ProductosModel::factory()->create(['precio' => 100]);
        ProductosModel::factory()->create(['precio' => 50]);
        ProductosModel::factory()->create(['precio' => 150]);

        // Ascending
        // Assuming a route like 'productos.ordenar'
        $responseAsc = $this->getJson(route('productos.ordenar', ['orden' => 'asc']));
        $responseAsc->assertStatus(200);
        $this->assertEquals(50, $responseAsc->json('data.0.precio'));

        // Descending
        // Assuming a route like 'productos.ordenar'
        $responseDesc = $this->getJson(route('productos.ordenar', ['orden' => 'desc']));
        $responseDesc->assertStatus(200);
        $this->assertEquals(150, $responseDesc->json('data.0.precio'));
    }
}
