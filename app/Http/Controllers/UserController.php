<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    //
    public function AllUsers(){
        $all = DB::table('users')->get();
        return view('frontend.users.all_users',compact('all'));
    }

    public function AddUser(){
        return view('frontend.users.add_user');
    }

    public function StoreUser(Request $request){
        $data = array();
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['password'] = bcrypt($request->password);
        DB::table('users')->insert($data);
        return redirect()->route('all.users');
    }

    public function EditUser($id){
        $edit = DB::table('users')->where('id', $id)->first();
        return view('frontend.users.edit_user', compact('edit'));
    }

    public function UpdateUser(Request $request, $id){
        $data = array();
        $data['name'] = $request->name;
        $data['email'] = $request->email;
        $data['password'] = bcrypt($request->password);

        try {
            DB::table('users')->where('id', $id)->update($data);
            return redirect()->route('all.users')->with('success', 'Usuario actualizado con éxito.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Hubo un error al actualizar el usuario: ' . $e->getMessage());
        }
    }

    public function DeleteUsers($id){
        DB::table('users')->where('id', $id)->delete();
        return redirect()->route('all.users');
    }
}
