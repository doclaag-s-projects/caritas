<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\File;
use App\Models\CategoryModel;
use App\Models\FileCategory;
use App\Models\FileTag;
use App\Models\Tag;
use Inertia\Inertia;

class FileController extends Controller
{
    // Subir archivo lógica. 
    public function upload(Request $request)
    {
        $categoriaPrincipalId = $request->input('categoria');
        $categoriaPrincipal = CategoryModel::find($categoriaPrincipalId);
        if (!$categoriaPrincipal) {
            return response()->json(['error' => 'Categoría no encontrada'], 400);
        }
        $categoriaPrincipalNombre = $categoriaPrincipal->nombre_categoria;
        $destinationPath = 'public/CARITASPRUEBASDOCS/' . $categoriaPrincipalNombre;

        // Verificar si la carpeta existe, si no, crearla
        if (!Storage::exists($destinationPath)) {
            Storage::makeDirectory($destinationPath);
        }

        // Verificar si se seleccionó una subcategoría
        $subcategoriaId = $request->input('subcategoria');
        if ($subcategoriaId) {
            $subcategoria = CategoryModel::find($subcategoriaId);
            if ($subcategoria && $subcategoria->categoria_principal == 0) {
                $subcategoriaNombre = $subcategoria->nombre_categoria;
                $destinationPath .= '/' . $subcategoriaNombre;

                // Verificar si la subcarpeta existe, si no, crearla
                if (!Storage::exists($destinationPath)) {
                    Storage::makeDirectory($destinationPath);
                }
            }
        }

        $file = $request->file('file');
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $cleanName = $this->validarNombre($originalName);
        $fileName = $cleanName . '.' . $extension;
        $filePath = $destinationPath . '/' . $fileName;

        // Verificar si el archivo ya existe
        $existingFile = File::where('nombre_archivo', $fileName)
                            ->where('ubicacion_archivo', Storage::url($filePath))
                            ->first();

        if ($existingFile) {
            if ($request->input('action') === 'replace') {
                Storage::delete($filePath);
                $file->storeAs($destinationPath, $fileName);
                $fileUrl = Storage::url($filePath);

                // Actualizar el registro del archivo existente
                $existingFile->update([
                    'ubicacion_archivo' => $fileUrl,
                    'estado' => $request->input('estado', 0),
                    'publico' => $request->input('publico', 0),
                ]);

                // Actualizar las etiquetas del archivo existente
                FileTag::where('archivo_id', $existingFile->id)->delete();
                $tags = $request->input('tag');
                if ($tags) {
                    $tagsArray = explode(',', $tags);
                    foreach ($tagsArray as $tag) {
                        FileTag::create([
                            'archivo_id' => $existingFile->id,
                            'etiqueta_id' => $tag,
                        ]);
                    }
                }

                return response()->json(['message' => 'Archivo reemplazado correctamente y registro actualizado'], 200);
            } elseif ($request->input('action') === 'rename') {
                // Obtener el nuevo nombre del archivo desde la solicitud
                $newFileName = $request->input('newFileName');
                if ($newFileName) {
                    $cleanNewFileName = $this->validarNombre($newFileName);
                    $fileName = $cleanNewFileName . '.' . $extension;
                    $filePath = $destinationPath . '/' . $fileName;
                } else {
                    $fileName = $cleanName . '_' . time() . '.' . $extension;
                    $filePath = $destinationPath . '/' . $fileName;
                }
            } else {
                return response()->json(['message' => 'El archivo ya existe', 'action' => 'exists'], 409);
            }
        }

        // Guardar el archivo en la carpeta correspondiente
        $file->storeAs($destinationPath, $fileName);
        $fileUrl = Storage::url($filePath);

        // Verificar si el usuario está autenticado
        if (Auth::check()) {
            $userId = Auth::id();
        } else {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        // Crear el registro del archivo
        $archivo = File::create([
            'nombre_archivo' => $fileName,
            'ubicacion_archivo' => $fileUrl,
            'estado' => $request->input('estado', 0),
            'publico' => $request->input('publico', 0),
            'usuarios_id' => $userId,
        ]);

        // Crear la etiqueta automática con el nombre del archivo
        $etiquetaAutomatica = Tag::create([
            'nombre_etiqueta' => $cleanName,
            'descripcion_etiqueta' => 'Etiqueta automática generada por el sistema',
            'automatico' => 1,
            'estado' => 1,
            'usuarios_id' => $userId,
        ]);
        
        // Crear la relación en la tabla archivos_etiquetas con la etiqueta automática
        FileTag::create([
            'archivo_id' => $archivo->id,
            'etiqueta_id' => $etiquetaAutomatica->id,
        ]);
        
        // Crear la relación en la tabla archivos_etiquetas si hay etiquetas adicionales
        $tags = $request->input('tag');
        if ($tags) {
            $tagsArray = explode(',', $tags);
            foreach ($tagsArray as $tag) {
                FileTag::create([
                    'archivo_id' => $archivo->id,
                    'etiqueta_id' => $tag,
                ]);
            }
        }

        // Crear la relación en la tabla archivos_categorias
        FileCategory::create([
            'archivo_id' => $archivo->id,
            'categoria_id' => $categoriaPrincipalId,
            'usuarios_id' => $userId,
        ]);
        if ($subcategoriaId) {
            FileCategory::create([
                'archivo_id' => $archivo->id,
                'categoria_id' => $subcategoriaId,
                'usuarios_id' => $userId,
            ]);
        }
        return response()->json(['message' => 'Archivo subido correctamente e insertado en la base de datos'], 200);
    }

    // Función para validar archivo nombre y cambiarlo.
    private function validarNombre($fileName)
    {
        $fileName = preg_replace('/\s+/', ' ', $fileName);
        $fileName = str_replace(' ', '_', $fileName);
        $search = ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'];
        $replace = ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'];
        $fileName = str_replace($search, $replace, $fileName);
        $fileName = str_replace(['Ñ', 'ñ'], ['N', 'n'], $fileName);
        $fileName = preg_replace('/[°!"#$%&\/(){}=¿?¡¨*\[\];:|\'´\-\+,.\/\\@¬~`^]/', '', $fileName);
        return $fileName;
    }

    // Listar Archivos
    public function list(Request $request)
    {
        $files = File::where('estado', '!=', 1)
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);

        $files->getCollection()->transform(function ($file) {
            return [
                'id' => $file->id,
                'name' => $file->nombre_archivo,
                'url' => $file->ubicacion_archivo,
                'estado' => $file->estado,
                'publico' => $file->publico,
                'usuarios_id' => $file->usuarios_id,
            ];
        });
        if ($request->wantsJson()) {
            return response()->json($files, 200);
        }
        return Inertia::render('Files/List', [
            'files' => $files,
        ]);
    }

    // Eliminar archivo lógica.
    public function delete(Request $request, $id)
    {
        $file = File::find($id);
        if (!$file) {
            return response()->json(['error' => 'Archivo no encontrado'], 404);
        }
        $file->update(['estado' => 1]);
        FileTag::where('archivo_id', $id)->delete();
        FileCategory::where('archivo_id', $id)->delete();

        return response()->json(['message' => 'Archivo eliminado correctamente'], 200);
    }
    // Renombrar archivo lógica. 
    public function rename(Request $request, $id)
    {
        $file = File::find($id);
        if (!$file) {
            return response()->json(['error' => 'Archivo no encontrado'], 404);
        }
    
        $newFileName = $request->input('newFileName');
        if (!$newFileName) {
            return response()->json(['error' => 'Nuevo nombre de archivo no proporcionado'], 400);
        }
    
        $cleanNewFileName = $this->validarNombre(pathinfo($newFileName, PATHINFO_FILENAME));
        $extension = pathinfo($file->nombre_archivo, PATHINFO_EXTENSION);
        $newFileNameWithExtension = $cleanNewFileName . '.' . $extension;
    
        // Obtener la ruta actual del archivo
        $currentFilePath = str_replace('/storage', 'public', $file->ubicacion_archivo);
        $newFilePath = dirname($currentFilePath) . '/' . $newFileNameWithExtension;
    
        // Mover el archivo a la nueva ubicación
        if (Storage::exists($currentFilePath)) {
            Storage::move($currentFilePath, $newFilePath);
        } else {
            return response()->json(['error' => 'Archivo físico no encontrado'], 404);
        }
    
        // Actualizar la ruta del archivo en la base de datos
        $fileUrl = Storage::url($newFilePath);
        $file->update([
            'nombre_archivo' => $newFileNameWithExtension,
            'ubicacion_archivo' => $fileUrl,
        ]);
    
        // Actualizar la etiqueta asociada con el nombre del archivo
        $originalFileName = $this->validarNombre(pathinfo($file->nombre_archivo, PATHINFO_FILENAME));
        $fileTags = FileTag::where('archivo_id', $file->id)->get();
        $tags = Tag::whereIn('id', $fileTags->pluck('etiqueta_id'))->where('automatico', 1)->get();
    
        foreach ($tags as $tag) {
            similar_text($originalFileName, $tag->nombre_etiqueta, $percent);
            if ($percent >= 90) {
                $tag->update([
                    'nombre_etiqueta' => $cleanNewFileName,
                ]);
                break;
            }
        }
    
        return response()->json(['message' => 'Archivo y etiqueta renombrados correctamente'], 200);
    }
    // Vista de archivos PDF función. 
    public function preview($id)
    {
        $file = File::find($id);
        if (!$file) {
            
            return response()->json(['error' => 'Archivo no encontrado'], 404);
        }

        return response()->json(['url' => $file->ubicacion_archivo], 200);
    }
}
