<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    // Listar todos los usuarios
    public function AllUsers(){
        $users = User::with('roles', 'permissions')->get();  // Cargar roles y permisos con el usuario
        return Inertia::render('Users/AllUsers', ['users' => $users]);
    }

    // Mostrar el formulario para agregar un nuevo usuario
    public function AddUser(){
        $roles = Role::all();
        $permissions = Permission::all();
        return Inertia::render('Users/AddUser', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    // Almacenar un nuevo usuario
    public function StoreUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'gender' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
            'permissions' => 'array|exists:permissions,name'
        ]);

        // Crear el usuario
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'gender' => $request->gender,
            'password' => bcrypt($request->password)
        ]);

        // Asignar el rol al usuario
        $user->assignRole($request->role);

        // Asignar permisos al usuario
        if ($request->has('permissions')) {
            $user->syncPermissions($request->permissions);  // syncPermissions asigna los permisos de manera segura
        }

        return redirect()->route('all.users')->with('success', 'Usuario creado con rol y permisos asignados correctamente.');
    }

    // Mostrar el formulario para editar un usuario
    public function EditUser($id){
        $user = User::with('roles', 'permissions')->findOrFail($id);
        $roles = Role::all();
        $permissions = Permission::all();

        return Inertia::render('Users/EditUser', [
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    // Actualizar un usuario existente
    public function UpdateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id, // Ignorar el email del propio usuario
            'gender' => 'required|string',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
            'permissions' => 'array|exists:permissions,name'
        ]);

        // Actualizar los datos del usuario
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'gender' => $request->gender,
            'password' => $request->password ? bcrypt($request->password) : $user->password,  // Actualizar solo si se cambia la contraseña
        ]);

        // Asignar nuevo rol al usuario
        $user->syncRoles([$request->role]);

        // Asignar nuevos permisos al usuario
        if ($request->has('permissions')) {
            $user->syncPermissions($request->permissions);
        }

        return redirect()->route('all.users')->with('success', 'Usuario actualizado con éxito y permisos/roles asignados correctamente.');
    }

    // Eliminar un usuario
    public function DeleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('all.users')->with('success', 'Usuario eliminado con éxito.');
    }
}
