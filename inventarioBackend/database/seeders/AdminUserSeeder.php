<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UsuariosModel;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder para crear el usuario administrador del sistema
 * 
 * Este seeder crea un único usuario admin que será el encargado
 * de gestionar todo el sistema de inventario.
 * 
 * Fecha de creación: 2025-11-18
 * Razón: Simplificar el sistema para un solo administrador
 */
class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existe un admin
        $adminExists = UsuariosModel::where('tipo', 'admin')->exists();

        if ($adminExists) {
            $this->command->info('Ya existe un usuario administrador en el sistema.');
            return;
        }

        // Crear el usuario administrador
        $admin = UsuariosModel::create([
            'correo' => 'admin@sistema.com',
            'nombre' => 'Administrador del Sistema',
            'contraseña' => Hash::make('Admin2024!'),
            'tipo' => 'admin'
        ]);

        $this->command->info('Usuario administrador creado exitosamente:');
        $this->command->info('Correo: admin@sistema.com');
        $this->command->info('Contraseña: Admin2024!');
        $this->command->warn('IMPORTANTE: Cambia la contraseña después del primer login.');
    }
}

