<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UsuarioRol;

class UserController extends Controller
{
    // Método para obtener todos los usuarios con sus roles
    public function show()
    {
        $users = User::with('usuariosRoles.role')->get();
        return response()->json($users);
    }

    // Método para actualizar la información del usuario
    public function update(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
    
            // Validar los datos del request
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email,' . $id,
                'password' => 'sometimes|string|min:8|confirmed',
                'gender' => 'required|string',
                'rol_id' => 'required|exists:roles,id',
                'Crear' => 'required|boolean',
                'Eliminar' => 'required|boolean',
                'Editar' => 'required|boolean',
            ]);
    
            // Actualizar la información del usuario
            $user->update([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => isset($validatedData['password']) ? bcrypt($validatedData['password']) : $user->password,
                'gender' => $validatedData['gender'],
                'Crear' => $validatedData['Crear'],
                'Eliminar' => $validatedData['Eliminar'],
                'Editar' => $validatedData['Editar'],
            ]);
    
            // Actualizar el rol del usuario
            UsuarioRol::where('usuario_id', $id)->delete();
            UsuarioRol::create([
                'usuario_id' => $user->id,
                'rol_id' => $validatedData['rol_id'],
            ]);
    
            // Recargar el usuario con los roles actualizados
            $user->load('usuariosRoles.role');
    
            return response()->json($user);
    
        } catch (\Exception $e) {
            // Manejar la excepción y retornar una respuesta de error
            return response()->json(['error' => 'Error al actualizar el usuario', 'message' => $e->getMessage()], 500);
        }
    }

    // Método para crear un nuevo usuario
    public function store(Request $request)
    {
        try {
            // Validar los datos del request
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'gender' => 'required|string',
                'rol_id' => 'required|exists:roles,id',
                'Crear' => 'required|boolean',
                'Eliminar' => 'required|boolean',
                'Editar' => 'required|boolean',
            ]);
    
            // Crear el nuevo usuario
            $user = User::create([
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => bcrypt($validatedData['password']),
                'gender' => $validatedData['gender'],
                'Crear' => $validatedData['Crear'],
                'Eliminar' => $validatedData['Eliminar'],
                'Editar' => $validatedData['Editar'],
            ]);
    
            // Asignar el rol al usuario
            UsuarioRol::create([
                'usuario_id' => $user->id,
                'rol_id' => $validatedData['rol_id'],
            ]);
    
            // Retornar una respuesta adecuada
            return response()->json($user, 201);
    
        } catch (\Exception $e) {
            // Manejar la excepción y retornar una respuesta de error
            return response()->json(['error' => 'Error al crear el usuario', 'message' => $e->getMessage()], 500);
        }
    }
}