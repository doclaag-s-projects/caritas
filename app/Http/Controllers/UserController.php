<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

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
        $user = User::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|string|min:8|confirmed',
            'gender' => 'required|string',
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,id',
            'terms' => 'accepted|required_if:Jetstream::hasTermsAndPrivacyPolicyFeature,true',
            'Crear' => 'required|boolean',
            'Eliminar' => 'required|boolean',
            'Editar' => 'required|boolean',
        ]);

        $user->update($validatedData);

        return response()->json($user);
    }
}