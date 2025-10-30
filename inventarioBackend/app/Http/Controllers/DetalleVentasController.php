<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DetalleVentasModel;
use App\Models\ventasModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class DetalleVentasController extends Controller
{
    /**
     * Genera un reporte de ventas detallado y con resumen, filtrado por fechas.
     *
     * Este endpoint es la base para dashboards y reportes en el frontend.
     * Provee un resumen general y un detalle de los productos más vendidos
     * dentro del rango de fechas especificado.
     */
    #[OA\Get(
        path: "/reportes/ventas",
        tags: ["Reportes"],
        summary: "Generar reporte de ventas",
        description: "Genera un reporte detallado de ventas con resumen general y detalle de productos vendidos",
        parameters: [
            new OA\Parameter(name: "fecha_inicio", in: "query", required: true, description: "Fecha de inicio (YYYY-MM-DD)", schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "fecha_fin", in: "query", required: true, description: "Fecha de fin (YYYY-MM-DD)", schema: new OA\Schema(type: "string", format: "date"))
        ]
    )]
    #[OA\Response(
        response: 200,
        description: "Reporte generado exitosamente",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "status", type: "string", example: "success"),
                new OA\Property(
                    property: "data",
                    type: "object",
                    properties: [
                        new OA\Property(property: "periodo", type: "object"),
                        new OA\Property(property: "resumen", type: "object", properties: [
                            new OA\Property(property: "numero_ventas", type: "integer", example: 45),
                            new OA\Property(property: "ingresos_totales", type: "number", example: 12500.50),
                            new OA\Property(property: "total_productos_vendidos", type: "integer", example: 150)
                        ]),
                        new OA\Property(property: "detalle_productos", type: "array", items: new OA\Items(type: "object"))
                    ]
                )
            ]
        )
    )]
    #[OA\Response(response: 400, description: "Fechas inválidas")]
    #[OA\Response(response: 500, description: "Error del servidor")]
    public function generarReporteVentas(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fecha_inicio' => 'required|date_format:Y-m-d',
            'fecha_fin' => 'required|date_format:Y-m-d|after_or_equal:fecha_inicio',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Fechas inválidas. Asegúrate de usar el formato YYYY-MM-DD.',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            $fechaInicio = $request->input('fecha_inicio');
            $fechaFin = $request->input('fecha_fin');

            // 1. Resumen General del Período
            $resumen = ventasModel::whereDate('fecha_venta', '>=', $fechaInicio)
                ->whereDate('fecha_venta', '<=', $fechaFin)
                ->select(
                    DB::raw('COUNT(id_ventas) as numero_ventas'),
                    DB::raw('SUM(total) as ingresos_totales')
                )
                ->first();

            // 2. Detalle de Productos Vendidos en el Período
            $detalleProductos = DetalleVentasModel::whereHas('venta', function ($query) use ($fechaInicio, $fechaFin) {
                $query->whereDate('fecha_venta', '>=', $fechaInicio)
                      ->whereDate('fecha_venta', '<=', $fechaFin);
            })
                ->with('producto:IdProducto,nombre,codigoProducto,cantidad_disponible') // Incluimos el stock actual
                ->select(
                    'id_producto',
                    DB::raw('SUM(cantidad) as total_unidades_vendidas'),
                    DB::raw('SUM(cantidad * precio) as ingresos_generados')
                )
                ->groupBy('id_producto')
                ->orderByDesc('total_unidades_vendidas')
                ->get();

            // Añadimos el conteo de unidades vendidas al resumen
            $resumen->total_productos_vendidos = $detalleProductos->sum('total_unidades_vendidas');

            return response()->json([
                'status' => 'success',
                'data' => [
                    'periodo' => ['inicio' => $fechaInicio, 'fin' => $fechaFin],
                    'resumen' => $resumen,
                    'detalle_productos' => $detalleProductos
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Error al generar el reporte', 'error' => $e->getMessage()], 500);
        }
    }
    
}
