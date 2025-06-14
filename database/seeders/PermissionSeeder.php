<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Permisos de roles y usuarios
            'create_role', 'edit_role', 'delete_role', 'view_role',
            'create_permission', 'edit_permission', 'delete_permission', 'view_permission',
            'create_user', 'edit_user', 'delete_user', 'view_user',

            // ✅ Permisos de tareas
            'ver_todas_las_tareas',
            'crear_tarea',
            'editar_tarea',
            'eliminar_tarea'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'api'
            ]);
        }
    }
}
