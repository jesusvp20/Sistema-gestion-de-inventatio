<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductosModel extends Model
{
    use HasFactory;

    public $table = 'producto';

    public $primaryKey = 'IdProducto';

    public $keyType = 'int';

    public $incrementing = true;

    // Laravel usará estas columnas en lugar de created_at / updated_at
    const CREATED_AT = 'fecha_creacion';

    const UPDATED_AT = 'fecha_actualizacion';

    public $timestamps = true;

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'cantidad_disponible',
        'categoria',
        'proveedor',
        'codigoProducto',
        'estado',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_actualizacion' => 'datetime',
        'precio' => 'decimal:2',
        'cantidad_disponible' => 'integer',
    ];

    /**
     * MODIFICADO: 2025-11-19
     * Cambio: Eliminado serializeDate para evitar conflictos con operaciones de BD
     * Razón: El formateo se hace manualmente en el controlador después de obtener los datos
     * Nota: Esto permite que las operaciones de BD (orderBy, whereDate, etc.) funcionen correctamente
     */

    // Relaciones
    public function proveedor()
    {
        return $this->belongsTo(ProveedorModel::class, 'proveedor', 'id');
    }

    public function detalleVentas()
    {
        return $this->hasMany(DetalleVentasModel::class, 'id_producto', 'IdProducto');
    }

    public function detalleFacturas()
    {
        return $this->hasMany(DetalleFacturaModel::class, 'producto_id', 'IdProducto');
    }
}
