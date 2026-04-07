<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // Crear usuario admin
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@monkits.com',
            'password' => Hash::make('password'),
            'active' => true,
        ]);

        // Crear usuario supervisor
        $supervisor = User::create([
            'name' => 'Supervisor',
            'email' => 'supervisor@monkits.com',
            'password' => Hash::make('password'),
            'active' => true,
        ]);

        // Crear usuario trabajador
        $worker = User::create([
            'name' => 'Trabajador',
            'email' => 'worker@monkits.com',
            'password' => Hash::make('password'),
            'active' => true,
        ]);

        // Crear usuario básico
        $user = User::create([
            'name' => 'Usuario',
            'email' => 'user@monkits.com',
            'password' => Hash::make('password'),
            'active' => true,
        ]);


        $ventas = User::create([
            'name' => 'ventas@monkits.com',
            'email' => 'ventas@monkits.com',
            'password' => '$2y$10$dET30G6c95Ne5y4RcK8Uq.SSjcZVrOyskMOe.oC3EgmG3gv0sV.9y',
            'active' => true,
        ]);

        $l_tellez = User::create([
            'name' => 'l.tellez@monkits.com',
            'email' => 'l.tellez@monkits.com',
            'password' => '$2y$10$KE2lIdnp6A1H1W0AaUjOw.sl5bPXfEggkENMIoynwPihghom0fInW',
            'active' => true,
        ]);

        $test2 = User::create([
            'name' => 'test2',
            'email' => 'user5@monkits.com',
            'password' => '$2y$10$MVe8mmoItbBRF4YhLbP1ju9PtNdyUg/QMIRcxkGCTVxJ/dHXlTmOe',
            'active' => true,
        ]);

        // Asignar roles (los roles ya deben estar creados por RolesSeeder)
        $admin->assignRole('admin');
        $ventas->assignRole('admin');
        $l_tellez->assignRole('admin');
        $test2->assignRole('admin');
        $supervisor->assignRole('supervisor');
        $worker->assignRole('worker');
        $user->assignRole('user');
    }
}