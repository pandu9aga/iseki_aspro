<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Type_User;

class MainController extends Controller
{
    public function index(){
        if (session()->has('Id_User')) {
            if (session('Id_Type_User') == 2){
                return redirect()->route('dashboard');
            }
            else if (session('Id_Type_User') == 1){
                return redirect()->route('home');
            }
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'NIK_User' => 'required',
            'Password_User' => 'required'
        ]);

        $user = User::where('NIK_User', $request->NIK_User)->first();

        if (!$user) {
            return back()->withErrors(['loginError' => 'Invalid username or password']);
        }

        if ($request->Password_User == $user->Password_User) {
            session(['Id_User' => $user->Id_User]);
            session(['Id_Type_User' => $user->Id_Type_User]);
            session(['NIK_User' => $user->NIK_User]);
            if (session('Id_Type_User') == 2){
                return redirect()->route('dashboard');
            }
            else if (session('Id_Type_User') == 1){
                return redirect()->route('home');
            }
        }

        return back()->withErrors(['loginError' => 'Invalid username or password']);
    }

    public function logout()
    {
        session()->forget('Id_User');
        session()->forget('Id_Type_User');
        session()->forget('NIK_User');
        return redirect()->route('/');
    }

    public function admin(){
        $type_user = Type_User::all();
        return view('admin', compact('type_user'));
    }

    public function create(Request $request){
        // melakukan validasi data
        $request->validate([
            'Name_User' => 'required',
            'NIK_User' => 'required|unique:users,NIK_User',
            'Password_User' => 'required',
            'Id_Type_User' => 'required'
        ],
        [
            'Name_User.required' => 'Nama wajib diisi',
            'NIK_User.required' => 'Username wajib diisi',
            'NIK_User.unique' => 'Username sudah digunakan, pilih yang lain',
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
        
        return redirect()->route('login');
    }
}