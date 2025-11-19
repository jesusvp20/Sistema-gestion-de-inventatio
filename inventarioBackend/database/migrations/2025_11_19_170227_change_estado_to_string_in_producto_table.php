<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = config('database.default');
        
        // Crear columna temporal para todos los casos
        Schema::table('producto', function (Blueprint $table) use ($driver) {
            if ($driver === 'sqlite') {
                $table->string('estado_temp', 20)->default('disponible');
            } else {
                $table->string('estado_temp', 20)->default('disponible');
            }
        });
        
        // Convertir los datos de boolean a string según el driver
        if ($driver === 'sqlite') {
            \DB::statement("UPDATE producto SET estado_temp = CASE 
                WHEN CAST(estado AS TEXT) = '1' THEN 'disponible' 
                WHEN CAST(estado AS TEXT) = '0' THEN 'agotado' 
                ELSE 'disponible' 
            END");
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: convertir boolean a string
            \DB::statement("UPDATE producto SET estado_temp = CASE 
                WHEN estado = true THEN 'disponible' 
                WHEN estado = false THEN 'agotado' 
                ELSE 'disponible' 
            END");
        } else {
            // MySQL y otros
            \DB::statement("UPDATE producto SET estado_temp = CASE 
                WHEN estado = 1 OR estado = true THEN 'disponible' 
                WHEN estado = 0 OR estado = false THEN 'agotado' 
                ELSE 'disponible' 
            END");
        }
        
        // Eliminar columna original y renombrar la temporal
        Schema::table('producto', function (Blueprint $table) {
            $table->dropColumn('estado');
        });
        
        Schema::table('producto', function (Blueprint $table) {
            $table->string('estado', 20)->default('disponible');
        });
        
        // Copiar datos de la temporal a la nueva
        \DB::statement("UPDATE producto SET estado = estado_temp");
        
        // Eliminar columna temporal
        Schema::table('producto', function (Blueprint $table) {
            $table->dropColumn('estado_temp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = config('database.default');
        
        // Convertir string a boolean antes de cambiar el tipo
        \DB::statement("UPDATE producto SET estado = CASE 
            WHEN estado = 'disponible' THEN 1 
            ELSE 0 
        END");
        
        if ($driver === 'sqlite') {
            Schema::table('producto', function (Blueprint $table) {
                $table->boolean('estado_temp')->default(true);
            });
            
            \DB::statement("UPDATE producto SET estado_temp = CASE WHEN estado = 'disponible' OR estado = '1' THEN 1 ELSE 0 END");
            
            Schema::table('producto', function (Blueprint $table) {
                $table->dropColumn('estado');
            });
            
            Schema::table('producto', function (Blueprint $table) {
                $table->boolean('estado')->default(true);
            });
            
            \DB::statement("UPDATE producto SET estado = estado_temp");
            
            Schema::table('producto', function (Blueprint $table) {
                $table->dropColumn('estado_temp');
            });
        } else {
            Schema::table('producto', function (Blueprint $table) {
                $table->boolean('estado')->default(true)->change();
            });
        }
    }
};
