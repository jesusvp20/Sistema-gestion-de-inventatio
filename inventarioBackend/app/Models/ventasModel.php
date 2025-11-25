<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ventasModel extends Model
{
    use HasFactory;

    public $table = 'ventas';

    public $primaryKey = 'id_ventas';

    public $incrementing = true;

    public $keyType = 'int';

    const CREATED_AT = 'fecha_venta';

    const UPDATED_AT = null;

    public $timestamps = true;

    protected $fillable = [
        'fecha_venta',
        'total',
        'id_cliente',
    ];

    protected $casts = [
        'fecha_venta' => 'datetime',
        'total' => 'decimal:2',
    ];

    /**
     * MODIFICADO: 2025-11-19
     * Cambio: Eliminado serializeDate para evitar conflictos con operaciones de BD
     * Razón: El formateo se hace manualmente en el controlador después de obtener los datos
     * Nota: Esto permite que las operaciones de BD (orderBy, whereDate, etc.) funcionen correctamente
     */

    public function cliente()
    {
        return $this->belongsTo(ClientesModel::class, 'id_cliente', 'id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleVentasModel::class, 'id_venta', 'id_ventas');
    }
}
