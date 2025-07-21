<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Type_User;

class UserController extends Controller
{
    public function index(){
        $page = "user";

        $user = User::all();
        $type_user = Type_User::all();
        return view('admins.users.index', compact('page', 'user', 'type_user'));
    }
    
    public function create(Request $request)
    {
        // melakukan validasi data
        $request->validate([
            'Name_User' => 'required',
            'NIK_User' => 'required|unique:users,NIK_User',
            'Password_User' => 'required',
            'Id_Type_User' => 'required'
        ],
        [
            'Name_User.required' => 'Nama wajib diisi',
            'NIK_User.required' => 'NIK wajib diisi',
            'NIK_User.unique' => 'NIK sudah digunakan, pilih yang lain',
            'Password_User.required' => 'Password wajib diisi',
            'Id_Type_User.required' => 'Type User wajib diisi'
        ]);
        
        //tambah data user
        DB::table('users')->insert([
            'Name_User' => $request->input('Name_User'),
            'NIK_User' => $request->input('NIK_User'),
            'Password_User' => $request->input('Password_User'),
            'Id_Type_User' => $request->input('Id_Type_User')
        ]);
        
        return redirect()->route('user');
    }

    public function update(Request $request, string $Id_User)
    {
        // melakukan validasi data
        $request->validate([
            'Name_User' => 'required',
            'NIK_User' => 'required|unique:users,NIK_User,' . $Id_User . ',Id_User',
            'Password_User' => 'required',
            'Id_Type_User' => 'required'
        ],
        [
            'Name_User.required' => 'Nama wajib diisi',
            'NIK_User.required' => 'NIK wajib diisi',
            'NIK_User.unique' => 'NIK sudah digunakan, pilih yang lain',
            'Password_User.required' => 'Password wajib diisi',
            'Id_Type_User.required' => 'Type User wajib diisi'
        ]);
    
        //update data user
        DB::table('users')->where('Id_User',$Id_User)->update([
            'Name_User' => $request->input('Name_User'),
            'NIK_User' => $request->input('NIK_User'),
            'Password_User' => $request->input('Password_User'),
            'Id_Type_User' => $request->input('Id_Type_User')
        ]);
                
        return redirect()->route('user');
    }

    public function destroy(User $Id_User)
    {
        $Id_User->delete();
        
        return redirect()->route('user')->with('success','Data berhasil di hapus' );
    }
}
