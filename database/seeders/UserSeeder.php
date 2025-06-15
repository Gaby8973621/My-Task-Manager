<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Crear roles
        foreach (['Super Admin', 'Admin', 'User'] as $rol) {
            Role::firstOrCreate(['name' => $rol, 'guard_name' => 'api']);
        }

        // Asignar permisos al rol Admin
        $adminRole = Role::findByName('Admin', 'api');
        $adminRole->givePermissionTo([
            'ver_todas_las_tareas',
            'crear_tarea',
            'editar_tarea',
            'eliminar_tarea'
        ]);

        //Asignar permiso al rol User para que pueda crear tarea
        $userRole = Role::findByName('User', 'api');
        $userRole->givePermissionTo([
            'crear_tarea'
        ]);

        // Crear usuarios con sus roles
        $superAdmin = User::firstOrCreate(
            ['email' => 'gabriela@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Gabi123$')
            ]
        );
        $superAdmin->assignRole('Super Admin');

        $admin = User::firstOrCreate(
            ['email' => 'stefany@gmail.com'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('Tefa123$')
            ]
        );
        $admin->assignRole('Admin');

        $user = User::firstOrCreate(
            ['email' => 'Tefa@gmail.com'],
            [
                'name' => 'Usuario',
                'password' => Hash::make('User123$')
            ]
        );
        $user->assignRole('User');
    }
}
