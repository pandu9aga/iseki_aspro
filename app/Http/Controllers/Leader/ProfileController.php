<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\Type_User;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileController extends Controller
{
    public function index()
    {
        $page = 'profile';

        $Id_User = session('Id_User');
        $user = User::find($Id_User);

        $type_user = Type_User::all();

        return view('leaders.profile.index', compact('page', 'user', 'type_user'));
    }

    public function update(Request $request, string $Id_User)
    {
        // melakukan validasi data
        $request->validate([
            'Name_User' => 'required',
            'Password_User' => 'required',
        ],
            [
                'Name_User.required' => 'Nama wajib diisi',
                'Password_User.required' => 'Password wajib diisi',
            ]);

        // update data user
        DB::table('users')->where('Id_User', $Id_User)->update([
            'Name_User' => $request->input('Name_User'),
            'Password_User' => $request->input('Password_User'),
        ]);

        return redirect()->route('profile');
    }
}
