<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * MODIFICADO: 2025-11-18 23:30:00
 * Cambio: Agregado $hidden para ocultar contraseña en respuestas JSON
 * Razón: Seguridad - Las contraseñas no deben exponerse en ninguna respuesta API
 */
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

    /**
     * Atributos que deben ocultarse en arrays/JSON
     * CRÍTICO PARA SEGURIDAD: La contraseña NUNCA debe aparecer en respuestas
     */
    protected $hidden = [
        'contraseña',
    ];

    protected $casts = [
        'contraseña' => 'hashed',
    ];
}

