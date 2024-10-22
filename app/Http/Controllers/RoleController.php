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
        ]);

        $role = Role::create($request->only('nombre'));

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
        ]);

        $role->update($request->only('nombre'));

        return response()->json($role);
    }

    public function destroy(Role $role)
    {
        $role->update(['estado' => 1]);

        return response()->json(['id' => $role->id], 200);
    }
}