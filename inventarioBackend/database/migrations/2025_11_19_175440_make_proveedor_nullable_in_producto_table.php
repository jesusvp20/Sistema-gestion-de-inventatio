<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = config('database.default');
        
        if ($driver === 'pgsql') {
            // PostgreSQL: Eliminar la restricción de clave foránea primero
            DB::statement('ALTER TABLE producto DROP CONSTRAINT IF EXISTS producto_proveedor_foreign');
            
            // Hacer la columna nullable
            Schema::table('producto', function (Blueprint $table) {
                $table->unsignedBigInteger('proveedor')->nullable()->change();
            });
            
            // Volver a agregar la clave foránea (las claves foráneas en PostgreSQL pueden ser nullable)
            DB::statement('ALTER TABLE producto ADD CONSTRAINT producto_proveedor_foreign 
                FOREIGN KEY (proveedor) REFERENCES proveedores(id)');
        } else {
            // Para MySQL y otros
            Schema::table('producto', function (Blueprint $table) {
                $table->unsignedBigInteger('proveedor')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = config('database.default');
        
        if ($driver === 'pgsql') {
            // PostgreSQL: Eliminar la restricción de clave foránea
            DB::statement('ALTER TABLE producto DROP CONSTRAINT IF EXISTS producto_proveedor_foreign');
            
            // Hacer la columna NOT NULL
            // Primero actualizar los valores NULL a un valor por defecto (por ejemplo, 1)
            DB::statement('UPDATE producto SET proveedor = 1 WHERE proveedor IS NULL');
            
            Schema::table('producto', function (Blueprint $table) {
                $table->unsignedBigInteger('proveedor')->nullable(false)->change();
            });
            
            // Volver a agregar la clave foránea
            DB::statement('ALTER TABLE producto ADD CONSTRAINT producto_proveedor_foreign 
                FOREIGN KEY (proveedor) REFERENCES proveedores(id)');
        } else {
            // Para MySQL y otros
            DB::statement('UPDATE producto SET proveedor = 1 WHERE proveedor IS NULL');
            
            Schema::table('producto', function (Blueprint $table) {
                $table->unsignedBigInteger('proveedor')->nullable(false)->change();
            });
        }
    }
};
