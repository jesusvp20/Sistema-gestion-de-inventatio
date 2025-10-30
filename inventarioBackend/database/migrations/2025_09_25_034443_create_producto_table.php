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
        Schema::create('producto', function (Blueprint $table) {
            $table->id('IdProducto');
            $table->string('nombre');
            $table->text('descripcion');
            $table->decimal('precio', 8, 2);
            $table->integer('cantidad_disponible');
            $table->string('categoria');
            $table->unsignedBigInteger('proveedor');
            $table->string('codigoProducto')->unique();
            $table->boolean('estado')->default(true);
            $table->timestamp('fecha_creacion')->nullable();
            $table->timestamp('fecha_actualizacion')->nullable();

            $table->foreign('proveedor')->references('id')->on('proveedores');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('producto');
    }
};
