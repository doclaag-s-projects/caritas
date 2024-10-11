<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategoryModel;
use Illuminate\Http\JsonResponse;
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
    
        $subcategoria->nombre_categoria = $request->input('nombre_categoria');
        $subcategoria->descripcion_categoria = $request->input('descripcion_categoria');
        $subcategoria->categoria_padre = $request->input('categoria_padre');
        $subcategoria->save();
    
        return response()->json(['message' => 'Subcategoría actualizada correctamente']);
    }


    // Eliminar una categoría
    public function destroy($id)
    {
        $categoria = CategoryModel::findOrFail($id);
        $categoria->delete();

        return redirect()->route('categories.index')->with('success', 'Categoría eliminada exitosamente.');
    }

}