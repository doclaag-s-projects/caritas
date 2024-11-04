<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return response()->json(Role::all());
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'estado' => 'required|boolean', // Valida que estado sea 0 o 1
        ]);
    
        $role = Role::create($request->only('nombre', 'estado'));
    
        return response()->json($role, 201);
    }
    
    public function show(Role $role)
    {
        return response()->json($role);
    }
    
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'estado' => 'required|boolean', // Valida que estado sea 0 o 1
        ]);

        $role->update($request->only('nombre', 'estado'));

        return response()->json($role);
    }

    public function destroy(Role $role)
    {
        // Cambia el estado del rol a 0 en lugar de eliminarlo
        $role->update(['estado' => 0]);

        return response()->json(['message' => 'Role desactivado exitosamente', 'id' => $role->id], 200);
    }
}