<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleVentasModel extends Model
{
    use HasFactory;

    public $table = 'detalle_ventas';

    public $primaryKey = 'id_detalle';

    public $incrementing = true;

    public $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_venta',
        'id_producto',
        'cantidad',
        'precio',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio' => 'decimal:2',
    ];

    // 🔗 Relaciones
    public function venta()
    {
        return $this->belongsTo(VentasModel::class, 'id_venta', 'id_ventas');
    }

    public function producto()
    {
        return $this->belongsTo(ProductosModel::class, 'id_producto', 'IdProducto');
    }
}
