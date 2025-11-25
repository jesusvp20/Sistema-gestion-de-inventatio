<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class facturaModel extends Model
{
    use HasFactory;

    protected $table = 'facturas';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'numero_facturas',
        'fecha',
        'cliente_id',
        'proveedor_id',
        'total',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'total' => 'float',
        'estado' => 'string', // MODIFICADO: 2025-11-24 - Cambiado de boolean a string para aceptar: pendiente, disponible, agotado
    ];

    /**
     * MODIFICADO: 2025-11-19
     * Cambio: Eliminado serializeDate para evitar conflictos con operaciones de BD
     * Razón: El formateo se hace manualmente en el controlador después de obtener los datos
     * Nota: Esto permite que las operaciones de BD (orderBy, whereDate, etc.) funcionen correctamente
     */

    public function cliente()
    {
        return $this->belongsTo(ClientesModel::class, 'cliente_id', 'id');
    }

    public function proveedor()
    {
        return $this->belongsTo(proveedorModel::class, 'proveedor_id', 'id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleFacturaModel::class, 'factura_id', 'id');
    }
}
