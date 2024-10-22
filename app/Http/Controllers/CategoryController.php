<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategoryModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function getCategorias(Request $request)
    {
        $categoriasPrincipales = CategoryModel::where('categoria_principal', 1)->get();
        $subcategorias = CategoryModel::where('categoria_principal', 0)->get();

        if ($request->wantsJson()) {
            return response()->json([
                'principales' => $categoriasPrincipales,
                'subcategorias' => $subcategorias
            ]);
        }

        return Inertia::render('Categories/Main', [
            'principales' => $categoriasPrincipales,
            'subcategorias' => $subcategorias,
        ]);
    }

    public function getSubcategorias(Request $request, $categoriaPadreId)
    {
        $subcategorias = CategoryModel::where('categoria_padre', $categoriaPadreId)->get();
        return response()->json($subcategorias);
    }

    public function getCategoriasPrincipales(Request $request)
    {
        $categoriasPrincipales = CategoryModel::where('categoria_principal', 1)->get();

        return response()->json($categoriasPrincipales);
    }

    //CAMBIOS PEDRO PABLO PARA LAS CATEGORIAS.
    public function obtenerCategoriasRecursivas(): JsonResponse
    {
        // Obtener las categorías recursivas desde el modelo
        $categorias = CategoryModel::obtenerCategoriasRecursivas();

        // Retornar los datos como respuesta JSON
        return response()->json($categorias);
    }

    // Métodos para obtener una categoría o subcategoría por ID
    public function getCategoriaById($id): JsonResponse
    {
        $categoria = CategoryModel::find($id);
        if (!$categoria) {
            return response()->json(['error' => 'Categoría no encontrada'], 404);
        }

        return response()->json($categoria);
    }

    public function getSubcategoriaById($id): JsonResponse
    {
        $subcategoria = CategoryModel::find($id);
        if (!$subcategoria) {
            return response()->json(['error' => 'Subcategoría no encontrada'], 404);
        }

        return response()->json($subcategoria);
    }

    public function CrearCategoriaAjax(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nombre_categoria' => 'required|string|max:255',
            'descripcion_categoria' => 'nullable|string',
        ]);

        if ($this->ExisteCategoriaPorNombre($validatedData['nombre_categoria'])) {
            return response()->json(['error' => 'La categoría ya existe'], 400);
        }

        $validatedData['categoria_principal'] = 1;
        $validatedData['categoria_padre'] = 0;

        $categoria = CategoryModel::create($validatedData);

        return response()->json([
            'message' => 'Categoría creada exitosamente.',
            'categoria' => $categoria
        ]);
    }

    public function CrearSubCategoriaAjax(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'nombre_categoria' => 'required|string|max:255',
            'descripcion_categoria' => 'nullable|string',
            'categoria_padre' => 'required|integer|exists:categorias,id',
        ]);

        if ($this->ExisteCategoriaPorNombre($validatedData['nombre_categoria'])) {
            return response()->json(['error' => 'La subcategoría ya existe'], 400);
        }

        $validatedData['categoria_principal'] = 0;

        $subcategoria = CategoryModel::create($validatedData);

        return response()->json([
            'message' => 'Subcategoría creada exitosamente.',
            'subcategoria' => $subcategoria
        ]);
    }

    public function updateCategoria(Request $request, $id)
    {
        $categoria = CategoryModel::find($id);
        if (!$categoria) {
            return response()->json(['error' => 'Categoría no encontrada'], 404);
        }

        if ($this->ExisteCategoriaPorNombre($request->input('nombre_categoria'))) {
            return response()->json(['error' => 'La categoría ya existe'], 400);
        }

        $categoria->nombre_categoria = $request->input('nombre_categoria');
        $categoria->descripcion_categoria = $request->input('descripcion_categoria');
        $categoria->save();

        return response()->json(['message' => 'Categoría actualizada correctamente']);
    }

    public function updateSubcategoria(Request $request, $id)
    {
        $subcategoria = CategoryModel::find($id);
        if (!$subcategoria) {
            return response()->json(['error' => 'Subcategoría no encontrada'], 404);
        }

        if ($this->ExisteCategoriaPorNombre($request->input('nombre_categoria'))) {
            return response()->json(['error' => 'La subcategoría ya existe'], 400);
        }

        $subcategoria->nombre_categoria = $request->input('nombre_categoria');
        $subcategoria->descripcion_categoria = $request->input('descripcion_categoria');
        $subcategoria->categoria_padre = $request->input('categoria_padre');
        $subcategoria->save();

        return response()->json(['message' => 'Subcategoría actualizada correctamente']);
    }


    public function ExisteCategoriaPorNombre(string $nombreCategoria): bool
    {
        // Consultar la base de datos para verificar si existe una categoría con el nombre proporcionado
        $existe = CategoryModel::where('nombre_categoria', $nombreCategoria)->exists();

        // Retornar true si existe, false si no
        return $existe;
    }

    // Eliminar una categoría
    public function destroy($id)
    {
        $categoria = CategoryModel::findOrFail($id);

        // Verificación 1: Validar que no tenga subcategorías
        if ($categoria->categoria_principal == 1) {
            $subcategorias = CategoryModel::where('categoria_padre', $id)->where('categoria_principal', 0)->exists();
            if ($subcategorias) {
                return response()->json(['error' => 'La categoría principal tiene subcategorías asociadas y no puede ser eliminada.'], 400);
            }
        }

        // Verificación 2: Validar que no tenga relación con archivos
        $archivoRelacion = DB::table('archivos_categorias')->where('categoria_id', $id)->first();
        if ($archivoRelacion) {
            $archivo = DB::table('archivos')->where('id', $archivoRelacion->archivo_id)->first();
            return response()->json(['error' => 'La categoría tiene relación con el archivo: ' . $archivo->nombre], 400);
        }

        // Verificación 3: Eliminar la categoría
        $categoria->delete();

        return response()->json(['message' => 'Categoría eliminada exitosamente.']);
    }
}