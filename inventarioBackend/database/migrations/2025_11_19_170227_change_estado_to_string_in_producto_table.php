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
        
        // Primero convertir los datos existentes de boolean a string
        if ($driver === 'sqlite') {
            \DB::statement("UPDATE producto SET estado = CASE 
                WHEN CAST(estado AS TEXT) = '1' THEN 'disponible' 
                WHEN CAST(estado AS TEXT) = '0' THEN 'agotado' 
                ELSE 'disponible' 
            END");
        } else {
            \DB::statement("UPDATE producto SET estado = CASE 
                WHEN estado = true OR estado = 1 THEN 'disponible' 
                WHEN estado = false OR estado = 0 THEN 'agotado' 
                ELSE 'disponible' 
            END");
        }
        
        // Para SQLite necesitamos recrear la tabla
        if ($driver === 'sqlite') {
            Schema::table('producto', function (Blueprint $table) {
                $table->string('estado_temp', 20)->default('disponible');
            });
            
            \DB::statement("UPDATE producto SET estado_temp = estado");
            
            Schema::table('producto', function (Blueprint $table) {
                $table->dropColumn('estado');
            });
            
            Schema::table('producto', function (Blueprint $table) {
                $table->string('estado', 20)->default('disponible');
            });
            
            \DB::statement("UPDATE producto SET estado = estado_temp");
            
            Schema::table('producto', function (Blueprint $table) {
                $table->dropColumn('estado_temp');
            });
        } else {
            // Para PostgreSQL, MySQL, etc. usar change()
            Schema::table('producto', function (Blueprint $table) {
                $table->string('estado', 20)->default('disponible')->change();
            });
        }
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
