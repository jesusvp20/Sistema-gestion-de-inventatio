<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Comando para truncar todas las tablas y reiniciar IDs desde 1
 * 
 * MODIFICADO: 2025-11-24
 * Cambio: Comando creado para limpiar base de datos y reiniciar secuencias
 */
class TruncarTablas extends Command
{
    protected $signature = 'db:truncate {--confirm : Confirmar sin preguntar}';
    protected $description = 'Trunca todas las tablas y reinicia los IDs desde 1';

    public function handle()
    {
        if (!$this->option('confirm')) {
            if (!$this->confirm('¿Estás seguro de que quieres truncar todas las tablas? Esta acción no se puede deshacer.')) {
                $this->info('Operación cancelada.');
                return 0;
            }
        }

        $this->info('Truncando tablas...');

        // Lista de tablas a truncar (en orden para respetar foreign keys)
        $tablas = [
            'detalle_facturas',
            'detalle_ventas',
            'facturas',
            'ventas',
            'producto',
            'clientes',
            'proveedores',
            'usuarios',
        ];

        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Para MySQL
        // Para PostgreSQL, usar TRUNCATE CASCADE

        foreach ($tablas as $tabla) {
            try {
                if (Schema::hasTable($tabla)) {
                    // Para PostgreSQL
                    if (config('database.default') === 'pgsql') {
                        DB::statement("TRUNCATE TABLE {$tabla} RESTART IDENTITY CASCADE;");
                    } else {
                        // Para MySQL
                        DB::table($tabla)->truncate();
                    }
                    $this->info("✓ Tabla {$tabla} truncada");
                }
            } catch (\Exception $e) {
                $this->error("✗ Error al truncar {$tabla}: " . $e->getMessage());
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Para MySQL

        $this->info('¡Todas las tablas han sido truncadas y los IDs reiniciados desde 1!');
        return 0;
    }
}


