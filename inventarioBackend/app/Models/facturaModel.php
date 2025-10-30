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
        'total',
        'estado',
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'total' => 'float',
        'estado' => 'boolean',
    ];

    public function cliente()
    {
        return $this->belongsTo(ClientesModel::class, 'cliente_id', 'id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleFacturaModel::class, 'factura_id', 'id');
    }
}
