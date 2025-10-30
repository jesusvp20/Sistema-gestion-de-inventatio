<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientesModel extends Model
{
    use HasFactory;

    public $table = 'clientes';

    public $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'identificacion',
        'email',
        'estado',
        'telefono',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    // relaciones
    public function ventas()
    {
        return $this->hasMany(ventasModel::class, 'cliente_id');
    }

    public function facturas()
    {
        return $this->hasMany(facturaModel::class, 'cliente_id', 'id');
    }
}
