<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleFacturaModel extends Model
{
    use HasFactory;

    protected $table = 'detallefactura';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'factura_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function factura()
    {
        return $this->belongsTo(facturaModel::class, 'factura_id', 'id');
    }

    public function producto()
    {
        return $this->belongsTo(ProductosModel::class, 'producto_id', 'IdProducto');
    }
}
