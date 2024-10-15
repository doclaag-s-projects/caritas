<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CategoryModel extends Model
{
    use HasFactory;

     // Define la tabla asociada al modelo si el nombre no sigue la convención de Laravel.
     protected $table = 'categorias';

     // Las columnas que el modelo puede llenar (Eloquent utilizará estas para las operaciones en masa).
     protected $fillable = [
        'id',
        'nombre_categoria',
        'descripcion_categoria',
        'categoria_principal',
        'categoria_padre'
     ];
     public function subcategorias()
     {
         return $this->hasMany(CategoryModel::class, 'categoria_padre');
     }
     // Método estático para obtener categorías con la consulta recursiva
    public static function obtenerCategoriasRecursivas()
    {
        $query = "
            WITH RECURSIVE category_tree AS (
                SELECT 
                    id, 
                    nombre_categoria, 
                    descripcion_categoria, 
                    categoria_principal, 
                    categoria_padre,
                    nombre_categoria AS full_path,
                    1 AS nivel
                FROM 
                    categorias
                WHERE 
                    categoria_padre = 0
                
                UNION ALL

                SELECT 
                    c.id, 
                    c.nombre_categoria, 
                    c.descripcion_categoria, 
                    c.categoria_principal, 
                    c.categoria_padre,
                    CONCAT(ct.full_path, ' -> ', c.nombre_categoria) AS full_path,
                    ct.nivel + 1 AS nivel
                FROM 
                    categorias c
                INNER JOIN 
                    category_tree ct ON c.categoria_padre = ct.id
            )
            SELECT * 
            FROM category_tree
            ORDER BY full_path, nivel;
        ";

        return DB::select($query);
    }
}
