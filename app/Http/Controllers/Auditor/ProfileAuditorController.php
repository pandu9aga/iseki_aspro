<?php

namespace App\Http\Controllers\Auditor;

use App\Http\Controllers\Controller;
use App\Models\Type_User;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileAuditorController extends Controller
{
    public function index()
    {
        $page = 'profile';

        $Id_User = session('Id_User');
        $user = User::find($Id_User);

        $type_user = Type_User::all();

        return view('auditors.profile.index', compact('page', 'user', 'type_user'));
    }

    public function update(Request $request, string $Id_User)
    {
        // melakukan validasi data
        $request->validate([
            'Name_User' => 'required',
        ],
            [
                'Name_User.required' => 'Nama wajib diisi',
            ]);

        // update data user
        DB::table('users')->where('Id_User', $Id_User)->update([
            'Name_User' => $request->input('Name_User'),
        ]);

        return redirect()->route('profile_auditor');
    }
}
