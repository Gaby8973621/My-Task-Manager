<?php

namespace Database\Seeders;

use App\Models\Tarea;
use App\Models\User;
use Illuminate\Database\Seeder;

class TareaSeeder extends Seeder
{
    public function run(): void
    {
        // Asegúrate de tener al menos un usuario
        $user = User::first();

        // Si no hay usuarios, detenemos el seeding
        if (!$user) {
            $this->command->info('No hay usuarios para asignar tareas.');
            return;
        }

        // Crear 5 tareas de ejemplo
        Tarea::factory()->count(5)->create([
            'user_id' => $user->id,
        ]);
    }
}
