<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migración para Laravel Sanctum - Personal Access Tokens
 * 
 * Esta migración crea la tabla necesaria para almacenar los tokens de autenticación API
 * que genera Laravel Sanctum cuando un usuario inicia sesión.
 * 
 * Fecha de creación: 2025-11-18 21:00:00
 * Razón: Solucionar error "relation personal_access_tokens does not exist"
 * 
 * IMPORTANTE: Esta migración debe ejecutarse en producción para que funcione el login
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable'); // Crea tokenable_type y tokenable_id
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};

