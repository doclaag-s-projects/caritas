<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class UserController extends Controller
{
    // Listar todos los usuarios
    public function AllUsers(){
        $all = DB::table('users')->get();
        return Inertia::render('Users/AllUsers', ['users' => $all]);
    }

    // Mostrar el formulario para agregar un nuevo usuario
    public function AddUser(){
        return Inertia::render('Users/AddUser');
    }

    // Almacenar un nuevo usuario
    public function StoreUser(Request $request){
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'gender' => $request->gender,
            'password' => bcrypt($request->password)
        ];
        DB::table('users')->insert($data);
        return redirect()->route('all.users');
    }

    // Editar un usuario
    public function EditUser($id){
        $edit = DB::table('users')->where('id', $id)->first();
        return Inertia::render('Users/EditUser', ['user' => $edit]);
    }

    // Actualizar un usuario existente
    public function UpdateUser(Request $request, $id){
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ];

        try {
            DB::table('users')->where('id', $id)->update($data);
            return redirect()->route('all.users')->with('success', 'Usuario actualizado con éxito.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Hubo un error al actualizar el usuario: ' . $e->getMessage());
        }
    }

    // Eliminar un usuario
    public function DeleteUser($id){
        DB::table('users')->where('id', $id)->delete();
        return redirect()->route('all.users');
    }
}
