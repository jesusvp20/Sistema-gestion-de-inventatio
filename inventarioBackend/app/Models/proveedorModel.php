<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class proveedorModel extends Model
{
    //
    use HasFactory;

    public $table = 'proveedores';

    public $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'estado',
    ];

    protected $casts = [
        'estado' => 'boolean',
    ];

    public function productos()
    {
        return $this->hasMany(ProductosModel::class, 'proveedor', 'id');
    }
    
}
