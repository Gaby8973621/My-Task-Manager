<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear roles si no existen (opcional)
        foreach (['Super Admin', 'Admin', 'User'] as $rol) {
            Role::firstOrCreate(['name' => $rol]);
        }

        // Usuario Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'gabriela@gmail.com',
            'password' => Hash::make('Gabi123$')
        ]);
        $superAdmin->assignRole('Super Admin');

        // Usuario Admin
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'stefany@gmail.com',
            'password' => Hash::make('Tefa123$')
        ]);
        $admin->assignRole('Admin');

        // Usuario normal
        $user = User::create([
            'name' => 'Usuario',
            'email' => 'Tefa@gmail.com',
            'password' => Hash::make('User123$')
        ]);
        $user->assignRole('User');
    }
}
