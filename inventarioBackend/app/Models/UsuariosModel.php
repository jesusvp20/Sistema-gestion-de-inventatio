<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
class UsuariosModel extends Model
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'usuarios';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'correo',
        'nombre',
        'contraseña',
        'tipo',
    ];

    protected $casts = [
        'contraseña' => 'hashed',
    ];
}
