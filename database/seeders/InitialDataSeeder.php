<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Horario;
use App\Models\Modalidad;
use App\Models\Workplace;
use App\Models\Departamento;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class InitialDataSeeder extends Seeder
{
    public function run()
    {
        // Solo crear si no existen datos

        // 1. ROLES
        if (Role::count() === 0) {
            $adminRole = Role::create(['nombre' => 'Administrador']);
            $userRole = Role::create(['nombre' => 'Trabajador']);
            $managerRole = Role::create(['nombre' => 'Jefe']);
            $hrRole = Role::create(['nombre' => 'Recursos Humanos']);
        } else {
            $adminRole = Role::where('nombre', 'Administrador')->first() ?? Role::first();
            $userRole = Role::where('nombre', 'Trabajador')->first() ?? Role::first();
            $managerRole = Role::where('nombre', 'Jefe')->first();
            $hrRole = Role::where('nombre', 'Recursos Humanos')->first();

            if (!$managerRole) {
                $managerRole = Role::create(['nombre' => 'Jefe']);
            }

            if (!$hrRole) {
                $hrRole = Role::create(['nombre' => 'Recursos Humanos']);
            }
        }

        // 2. MODALIDADES
        if (Modalidad::count() === 0) {
            Modalidad::create(['nombre' => 'Presencial']);
            Modalidad::create(['nombre' => 'Teletrabajo']);
            Modalidad::create(['nombre' => 'Hibrido']);
        }

        // 3. WORKPLACES
        if (Workplace::count() === 0) {
            Workplace::create(['nombre' => 'Oficina Central', 'coordenadas' => '40.4168,-3.7038']);
            Workplace::create(['nombre' => 'Sucursal Norte', 'coordenadas' => '40.4500,-3.6900']);
        }

        // 4. DEPARTAMENTOS
        if (Departamento::count() === 0) {
            Departamento::create(['nombre' => 'Tecnologia']);
            Departamento::create(['nombre' => 'Recursos Humanos']);
            Departamento::create(['nombre' => 'Ventas']);
        }

        // 5. HORARIOS
        if (Horario::count() === 0) {
            Horario::create(['nombre' => 'Estandar', 'horas' => '09:00 - 18:00', 'dias' => 'L-V']);
            Horario::create(['nombre' => 'Intensivo', 'horas' => '08:00 - 15:00', 'dias' => 'L-V']);
        }

        // 6. USUARIO ADMIN
        if (User::where('email', 'admin@admin.com')->doesntExist()) {
            User::create([
                'nombre' => 'Admin',
                'apellidos' => 'Sistema',
                'email' => 'admin@admin.com',
                'password' => Hash::make('password'),
                'telefono' => '000000000',
                'id_rol' => $adminRole ? $adminRole->id : null,
                'is_presente' => false
            ]);
        }

        if (User::where('email', 'juan@demo.com')->doesntExist()) {
            User::create([
                'nombre' => 'Juan',
                'apellidos' => 'Perez',
                'email' => 'juan@demo.com',
                'password' => Hash::make('password'),
                'telefono' => '123456789',
                'id_rol' => $userRole ? $userRole->id : null,
                'is_presente' => true
            ]);
        }

        if (User::where('email', 'jefe@demo.com')->doesntExist()) {
            User::create([
                'nombre' => 'Carlos',
                'apellidos' => 'Gomez',
                'email' => 'jefe@demo.com',
                'password' => Hash::make('password'),
                'telefono' => '600111222',
                'id_rol' => $managerRole ? $managerRole->id : null,
                'is_presente' => true
            ]);
        }

        if (User::where('email', 'rrhh@demo.com')->doesntExist()) {
            User::create([
                'nombre' => 'Laura',
                'apellidos' => 'Martinez',
                'email' => 'rrhh@demo.com',
                'password' => Hash::make('password'),
                'telefono' => '600333444',
                'id_rol' => $hrRole ? $hrRole->id : null,
                'is_presente' => true
            ]);
        }

        echo "Datos iniciales creados correctamente.\n";
    }
}
