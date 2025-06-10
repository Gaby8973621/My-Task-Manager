<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        $superAdmin = User::create(attributes: [
            'name' => 'Super Admin',
            'email' => 'gabriela@gmail.com',
            'password' => Hash::make('Gabi123$')
        ]);

        $superAdmin->assignRole('Super Admin');

        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'stefany@gmail.com',
            'password' => Hash::make('Tefa123$')
        ]);

        $admin->assignRole('Admin');
    }
}
