<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:pdf,doc,docx|max:10240', //máx. 10 MB
        ]);

        $file = $request->file('file');
        $destinationPath = 'public/CARITASPRUEBASDOCS';

        if (!Storage::exists($destinationPath)) {
            Storage::makeDirectory($destinationPath);
        }
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $cleanName = $this->validarNombre($originalName);
        $fileName = $cleanName . '.' . $extension;
        $file->storeAs($destinationPath, $fileName);

        return response()->json(['message' => 'Archivo subido correctamente'], 200);
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
}