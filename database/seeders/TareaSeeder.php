<?php

namespace Database\Seeders;

use App\Models\Tarea;
use App\Models\User;
use Illuminate\Database\Seeder;

class TareaSeeder extends Seeder
{
    public function run(): void
    {
        // usuarios con rol 'User'
        $usuarios = User::role('User')->get();

        if ($usuarios->isEmpty()) {
            $this->command->info('No hay usuarios con el rol User para asignar tareas.');
            return;
        }

        foreach ($usuarios as $user) {
            // Crear 5 tareas por usuario
            $tareas = Tarea::factory()->count(5)->make();

            // Asignar user_id a cada tarea y guardar
            $tareas->each(function ($tarea) use ($user) {
                $tarea->user_id = $user->id;
                $tarea->save();
            });
        }

        $this->command->info('Se crearon tareas para usuarios con rol User.');
    }
}
