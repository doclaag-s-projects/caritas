<?php

namespace Database\Seeders;

use App\Models\User;
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
            'gender' => 'male',
            'password' => Hash::make('1234'), 
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

        // Insertar categorías principales
        DB::table('categorias')->insert([
            ['nombre_categoria' => 'Área social', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área social', 'categoria_principal' => 1, 'categoria_padre' => 0],
            ['nombre_categoria' => 'Área Ambiental/Agropecuario', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área Ambiental/Agropecuario', 'categoria_principal' => 1, 'categoria_padre' => 0],
            ['nombre_categoria' => 'Área de Salud', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área de Salud', 'categoria_principal' => 1, 'categoria_padre' => 0],
            ['nombre_categoria' => 'Área de Educación', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área de Educación', 'categoria_principal' => 1, 'categoria_padre' => 0],
            ['nombre_categoria' => 'Planes Marcos PSC', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Planes Marcos PSC', 'categoria_principal' => 1, 'categoria_padre' => 0],
            ['nombre_categoria' => 'Territorios/Área de cobertura', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Territorios/Área de cobertura', 'categoria_principal' => 1, 'categoria_padre' => 0],
        ]);

        // Insertar subcategorías del Área social
        DB::table('categorias')->insert([
            ['nombre_categoria' => 'Participación ciudadana e incidencia', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área social', 'categoria_principal' => 0, 'categoria_padre' => 1],
            ['nombre_categoria' => 'Legislación Guatemalteca', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área social', 'categoria_principal' => 0, 'categoria_padre' => 1],
            ['nombre_categoria' => 'Organización', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área social', 'categoria_principal' => 0, 'categoria_padre' => 1],
            ['nombre_categoria' => 'Documentos sobre la doctrina social de la iglesia', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área social', 'categoria_principal' => 0, 'categoria_padre' => 1],
        ]);

        // Insertar subcategorías del Área Ambiental/Agropecuario
        DB::table('categorias')->insert([
            ['nombre_categoria' => 'Producción agrícola', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área Ambiental/Agropecuario', 'categoria_principal' => 0, 'categoria_padre' => 2],
            ['nombre_categoria' => 'Producción pecuaria', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área Ambiental/Agropecuario', 'categoria_principal' => 0, 'categoria_padre' => 2],
            ['nombre_categoria' => 'Ambiente y RR NN', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área Ambiental/Agropecuario', 'categoria_principal' => 0, 'categoria_padre' => 2],
            ['nombre_categoria' => 'Transformación de productos', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área Ambiental/Agropecuario', 'categoria_principal' => 0, 'categoria_padre' => 2],
            ['nombre_categoria' => 'Comercialización', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área Ambiental/Agropecuario', 'categoria_principal' => 0, 'categoria_padre' => 2],
        ]);

        // Insertar subcategorías del Área de Salud
        DB::table('categorias')->insert([
            ['nombre_categoria' => 'Seguridad Alimentaria', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área de Salud', 'categoria_principal' => 0, 'categoria_padre' => 3],
            ['nombre_categoria' => 'Medicina Alternativa', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área de Salud', 'categoria_principal' => 0, 'categoria_padre' => 3],
            ['nombre_categoria' => 'Salud Mental', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área de Salud', 'categoria_principal' => 0, 'categoria_padre' => 3],
            ['nombre_categoria' => 'Nutrición Materno/Infantil', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área de Salud', 'categoria_principal' => 0, 'categoria_padre' => 3],
            ['nombre_categoria' => 'Medicina General/Estadísticas de Salud', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área de Salud', 'categoria_principal' => 0, 'categoria_padre' => 3],
            ['nombre_categoria' => 'Higiene', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área de Salud', 'categoria_principal' => 0, 'categoria_padre' => 3],
            ['nombre_categoria' => 'Agua y Saneamiento', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área de Salud', 'categoria_principal' => 0, 'categoria_padre' => 3],
            ['nombre_categoria' => 'Gestión de riesgos', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área de Salud', 'categoria_principal' => 0, 'categoria_padre' => 3],
        ]);

        // Insertar subcategorías del Área de Educación
        DB::table('categorias')->insert([
            ['nombre_categoria' => 'Educación', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área de Educación', 'categoria_principal' => 0, 'categoria_padre' => 4],
            ['nombre_categoria' => 'Alimentación Escolar', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área de Educación', 'categoria_principal' => 0, 'categoria_padre' => 4],
            ['nombre_categoria' => 'Infraestructura Comunitaria', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Área de Educación', 'categoria_principal' => 0, 'categoria_padre' => 4],
        ]);

        // Insertar subcategorías de Planes Marcos PSC
        DB::table('categorias')->insert([
            ['nombre_categoria' => 'PEI', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Planes Marcos PSC', 'categoria_principal' => 0, 'categoria_padre' => 5],
            ['nombre_categoria' => 'Agenda Política', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Planes Marcos PSC', 'categoria_principal' => 0, 'categoria_padre' => 5],
            ['nombre_categoria' => 'Políticas Institucionales', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Planes Marcos PSC', 'categoria_principal' => 0, 'categoria_padre' => 5],
            ['nombre_categoria' => 'Historia PSC', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Planes Marcos PSC', 'categoria_principal' => 0, 'categoria_padre' => 5],
        ]);

        // Insertar subcategorías de Territorios/Área de cobertura
        DB::table('categorias')->insert([
            ['nombre_categoria' => 'Diagnósticos', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Territorios/Área de cobertura', 'categoria_principal' => 0, 'categoria_padre' => 6],
            ['nombre_categoria' => 'Planes de comunicación', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Territorios/Área de cobertura', 'categoria_principal' => 0, 'categoria_padre' => 6],
            ['nombre_categoria' => 'Informes anuales', 'descripcion_categoria' => 'Categoría Generada De Forma Automática: Territorios/Área de cobertura', 'categoria_principal' => 0, 'categoria_padre' => 6],
        ]);
    }
}