<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\ClientesModel;

class ClientesControllerTest extends TestCase
{
    use RefreshDatabase; // Usa una base de datos en memoria para las pruebas

    /** @test */
    public function puede_listar_clientes()
    {
        ClientesModel::factory()->count(3)->create();
        // Use the route name for consistency and to avoid hardcoded URLs
        $response = $this->getJson(route('clientes.index'));

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'nombre', 'email', 'identificacion', 'telefono', 'estado']
                ]
            ]);
    }

    /** @test */
    public function puede_crear_un_cliente()
    {
        $clienteData = [
            'nombre' => 'Cliente de Prueba',
            'email' => 'prueba@example.com',
            'identificacion' => '123456789',
            'telefono' => '555-1234',
            'estado' => true,
        ];

        $response = $this->postJson(route('clientes.store'), $clienteData);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Cliente creado exitosamente',
            ])
            ->assertJsonPath('data.nombre', 'Cliente de Prueba');

        $this->assertDatabaseHas('clientes', ['email' => 'prueba@example.com']);
    }

    /** @test */
    public function puede_mostrar_un_cliente_por_id()
    {
        $cliente = ClientesModel::factory()->create([
            'nombre' => 'Cliente Test',
            'email' => 'test@example.com',
            'identificacion' => '987654321',
            'telefono' => '555-9876',
            'estado' => true,
        ]);

        $response = $this->getJson("/api/clientes/{$cliente->id}");

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Cliente obtenido exitosamente',
            ])
            ->assertJsonPath('data.id', $cliente->id)
            ->assertJsonPath('data.nombre', 'Cliente Test')
            ->assertJsonPath('data.email', 'test@example.com')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['id', 'nombre', 'email', 'identificacion', 'telefono', 'estado'],
                'statusCode'
            ]);
    }

    /** @test */
    public function retorna_404_cuando_cliente_no_existe()
    {
        $response = $this->getJson('/api/clientes/99999');

        $response->assertStatus(404)
            ->assertJson([
                'status' => 'error',
            ])
            ->assertJsonPath('statusCode', 404);
    }

    /** @test */
    public function puede_actualizar_un_cliente()
    {
        $cliente = ClientesModel::factory()->create([
            'nombre' => 'Cliente Original',
            'email' => 'original@example.com',
            'identificacion' => '111111111',
            'telefono' => '555-0000',
            'estado' => true,
        ]);

        $updateData = [
            'nombre' => 'Nombre Actualizado',
            'email' => 'actualizado@example.com',
            'identificacion' => '999999999',
            'telefono' => '555-9999',
            'estado' => false,
        ];

        $response = $this->putJson("/api/clientes/{$cliente->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Cliente actualizado exitosamente',
            ])
            ->assertJsonPath('data.nombre', 'Nombre Actualizado')
            ->assertJsonPath('data.email', 'actualizado@example.com')
            ->assertJsonPath('data.identificacion', '999999999')
            ->assertJsonPath('data.telefono', '555-9999')
            ->assertJsonPath('data.estado', false);

        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id,
            'nombre' => 'Nombre Actualizado',
            'email' => 'actualizado@example.com',
            'identificacion' => '999999999',
            'telefono' => '555-9999',
            'estado' => false,
        ]);
    }

    /** @test */
    public function puede_actualizar_solo_algunos_campos_del_cliente()
    {
        $cliente = ClientesModel::factory()->create([
            'nombre' => 'Cliente Original',
            'email' => 'original@example.com',
            'identificacion' => '111111111',
            'telefono' => '555-0000',
            'estado' => true,
        ]);

        // Actualizar solo email y estado
        $updateData = [
            'email' => 'nuevoemail@example.com',
            'estado' => false,
        ];

        $response = $this->putJson("/api/clientes/{$cliente->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonPath('data.email', 'nuevoemail@example.com')
            ->assertJsonPath('data.estado', false)
            ->assertJsonPath('data.nombre', 'Cliente Original') // Debe mantener el nombre original
            ->assertJsonPath('data.identificacion', '111111111'); // Debe mantener la identificación original

        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id,
            'email' => 'nuevoemail@example.com',
            'estado' => false,
            'nombre' => 'Cliente Original',
            'identificacion' => '111111111',
        ]);
    }

    /** @test */
    public function puede_eliminar_un_cliente()
    {
        $cliente = ClientesModel::factory()->create();

        $response = $this->deleteJson(route('clientes.destroy', $cliente->id));

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonPath('statusCode', 200)
            ->assertJsonStructure([
                'status',
                'message',
                'statusCode'
            ]);

        $this->assertDatabaseMissing('clientes', ['id' => $cliente->id]);
    }

    /** @test */
    public function puede_cambiar_el_estado_de_un_cliente()
    {
        $cliente = ClientesModel::factory()->create(['estado' => true]);

        $response = $this->patchJson("/api/clientes/{$cliente->id}/estado");

        $response->assertStatus(200)
            ->assertJsonPath('data.estado', false);

        $this->assertDatabaseHas('clientes', ['id' => $cliente->id, 'estado' => false]);
    }

    /** @test */
    public function puede_buscar_clientes_por_nombre()
    {
        ClientesModel::factory()->create(['nombre' => 'Cliente Buscado']);
        ClientesModel::factory()->create(['nombre' => 'Otro Cliente']);

        $response = $this->getJson('/api/clientes/buscar?nombre=Buscado');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.nombre', 'Cliente Buscado');
    }

    /** @test */
    public function puede_listar_clientes_activos()
    {
        ClientesModel::factory()->create(['estado' => true]);
        ClientesModel::factory()->create(['estado' => false]);
        ClientesModel::factory()->create(['estado' => true]);

        $response = $this->getJson('/api/clientes/activos');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
