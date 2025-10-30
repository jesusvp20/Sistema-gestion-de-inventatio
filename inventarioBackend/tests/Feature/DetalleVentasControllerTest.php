<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\ventasModel;
use App\Models\DetalleVentasModel;
use App\Models\ProductosModel;
use App\Models\ClientesModel;
use Carbon\Carbon;

class DetalleVentasControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function puede_generar_un_reporte_de_ventas()
    {
        // Crear datos de prueba
        $producto1 = ProductosModel::factory()->create();
        $producto2 = ProductosModel::factory()->create();

        $ventaPasada = ventasModel::factory()->create(['fecha_venta' => Carbon::now()->subDays(10)]);
        DetalleVentasModel::factory()->create(['id_venta' => $ventaPasada->id_ventas, 'id_producto' => $producto1->IdProducto, 'cantidad' => 2]);

        $ventaReciente1 = ventasModel::factory()->create(['fecha_venta' => Carbon::now()->subDay()]);
        DetalleVentasModel::factory()->create(['id_venta' => $ventaReciente1->id_ventas, 'id_producto' => $producto1->IdProducto, 'cantidad' => 3]);
        DetalleVentasModel::factory()->create(['id_venta' => $ventaReciente1->id_ventas, 'id_producto' => $producto2->IdProducto, 'cantidad' => 5]);
        
        $ventaReciente2 = ventasModel::factory()->create(['fecha_venta' => Carbon::now()]);
        DetalleVentasModel::factory()->create(['id_venta' => $ventaReciente2->id_ventas, 'id_producto' => $producto1->IdProducto, 'cantidad' => 1]);

        $fechaInicio = Carbon::now()->subDays(5)->toDateString();
        $fechaFin = Carbon::now()->toDateString();

        $response = $this->getJson("/api/reportes/ventas?fecha_inicio={$fechaInicio}&fecha_fin={$fechaFin}");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.periodo.inicio', $fechaInicio)
            ->assertJsonPath('data.periodo.fin', $fechaFin)
            ->assertJsonPath('data.resumen.numero_ventas', 2)
            ->assertJsonPath('data.resumen.total_productos_vendidos', 9) // 3 + 5 + 1
            ->assertJsonCount(2, 'data.detalle_productos');
    }

    /** @test */
    public function devuelve_error_con_fechas_invalidas()
    {
        $response = $this->getJson('/api/reportes/ventas?fecha_inicio=abc&fecha_fin=def');

        $response->assertStatus(400)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Fechas inválidas. Asegúrate de usar el formato YYYY-MM-DD.');
    }
}
