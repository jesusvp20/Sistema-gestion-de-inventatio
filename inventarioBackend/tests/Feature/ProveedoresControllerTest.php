<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\proveedorModel;

class ProveedoresControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function puede_listar_proveedores()
    {
        proveedorModel::factory()->count(3)->create();

        $response = $this->getJson('/api/proveedores');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'nombre', 'direccion', 'telefono', 'estado']
                ]
            ]);
    }

    /** @test */
    public function puede_crear_un_proveedor()
    {
        $proveedorData = [
            'nombre' => 'Proveedor de Prueba',
            'direccion' => 'Calle Falsa 123',
            'telefono' => '555-5678',
            'estado' => true,
        ];

        $response = $this->postJson('/api/proveedores', $proveedorData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Proveedor creado exitosamente',
            ])
            ->assertJsonPath('data.nombre', 'Proveedor de Prueba');

        $this->assertDatabaseHas('proveedores', ['nombre' => 'Proveedor de Prueba']);
    }

    /** @test */
    public function puede_actualizar_un_proveedor()
    {
        $proveedor = proveedorModel::factory()->create();

        $updateData = ['nombre' => 'Nombre Proveedor Actualizado'];

        $response = $this->putJson("/api/proveedores/{$proveedor->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Proveedor actualizado exitosamente',
            ])
            ->assertJsonPath('data.nombre', 'Nombre Proveedor Actualizado');

        $this->assertDatabaseHas('proveedores', ['id' => $proveedor->id, 'nombre' => 'Nombre Proveedor Actualizado']);
    }

    /** @test */
    public function puede_eliminar_un_proveedor()
    {
        $proveedor = proveedorModel::factory()->create();

        $response = $this->deleteJson("/api/proveedores/{$proveedor->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'El proveedor se ha eliminado correctamente',
            ]);

        $this->assertDatabaseMissing('proveedores', ['id' => $proveedor->id]);
    }

    /** @test */
    public function puede_cambiar_el_estado_de_un_proveedor()
    {
        $proveedor = proveedorModel::factory()->create(['estado' => true]);

        $response = $this->patchJson("/api/proveedores/{$proveedor->id}/estado");

        $response->assertStatus(200)
            ->assertJsonPath('data.estado', false);

        $this->assertDatabaseHas('proveedores', ['id' => $proveedor->id, 'estado' => false]);
    }

    /** @test */
    public function puede_buscar_proveedores_por_nombre()
    {
        proveedorModel::factory()->create(['nombre' => 'Proveedor Buscado']);
        proveedorModel::factory()->create(['nombre' => 'Otro Proveedor']);

        $response = $this->getJson('/api/proveedores/buscar?nombre=Buscado');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre', 'Proveedor Buscado');
    }

    /** @test */
    public function puede_listar_proveedores_activos()
    {
        proveedorModel::factory()->create(['estado' => true]);
        proveedorModel::factory()->create(['estado' => false]);
        proveedorModel::factory()->create(['estado' => true]);

        $response = $this->getJson('/api/proveedores/activos');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
