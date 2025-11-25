<?php

namespace App\documentacion;

use OpenApi\Attributes as OA;

/**
 * Documentación Swagger para Detalles de Factura
 * MODIFICADO: 2025-11-24
 * Cambio: Endpoints de detalles eliminados - Los detalles se gestionan dentro de los endpoints de facturas
 * Razón: Simplificación de la API - Solo 4 endpoints esenciales para facturas
 * NOTA: Este archivo se mantiene por compatibilidad pero los endpoints ya no están activos
 */
class DetalleFacturaDocs
{
    // Los endpoints de detalles individuales han sido eliminados
    // Los detalles ahora se gestionan dentro de los endpoints principales de facturas:
    // - POST /facturas - Crear factura con detalles
    // - PUT /facturas/{id} - Actualizar factura y detalles
}

