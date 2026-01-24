<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileMemberController extends Controller
{
    public function index()
    {
        $page = 'profile';

        $Id_Member = session('Id_Member');
        $member = Member::find($Id_Member);

        return view('members.profile.index', compact('page', 'member'));
    }

    public function update(Request $request, string $Id_Member)
    {
        // melakukan validasi data
        $request->validate([
            'Name_Member' => 'required',
        ],
            [
                'Name_Member.required' => 'Nama wajib diisi',
            ]);

        // update data membet
        DB::table('members')->where('Id_Member', $Id_Member)->update([
            'Name_Member' => $request->input('Name_Member'),
        ]);

        return redirect()->route('profile_member');
    }
}
