<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\UsuarioRol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear el rol ADMIN y obtener su ID
        $roleId = DB::table('roles')->insertGetId([
            'nombre' => 'ADMIN',
            'estado' => 1,
        ]);

        // Crear el usuario y obtener su ID
        $userId = User::factory()->create([
            'name' => 'TEST',
            'email' => 'test@com',
            'gender' => 'male', // o 'female' según corresponda
            'password' => Hash::make('1234'), // Asegúrate de usar Hash::make para encriptar la contraseña
            'Crear' => true,
            'Eliminar' => true,
            'Editar' => true,
            'current_team_id' => null,
            'profile_photo_path' => null,
            'estado' => 1,
        ])->id;

        // Crear la relación en la tabla usuarios_roles
        UsuarioRol::create([
            'usuario_id' => $userId,
            'rol_id' => $roleId,
        ]);
    }
}