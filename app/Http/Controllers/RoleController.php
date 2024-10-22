<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    //
    // Método para obtener todos los roles y permisos
    public function allRolesAndPermissions()
    {
        $roles = Role::all();
        $permissions = Permission::all();

        return inertia('Role/Index', [
            'roles' => $roles,
            'permissions' => $permissions,
        ]);
    }

    // Método para crear roles
    public function createRole(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'permissions' => 'array|exists:permissions,name'
        ]);

        $role = Role::create(['name' => $request->name]);

        // Asignar permisos al rol
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return inertia('Role/Create', [
            'role' => $role,
            'message' => 'Rol creado y permisos asignados correctamente',
        ]);
    }

    // Método para crear permisos
    public function createPermission(Request $request)
    {
        // Validar el nombre del permiso
        $request->validate([
            'name' => 'required|string|unique:permissions,name'
        ]);

        // Crear el permiso
        Permission::create(['name' => $request->name]);

        return response()->json(['message' => 'Permiso creado satisfactoriamente.']);
    }
}
