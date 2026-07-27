<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Helpers\MemberHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfileMemberController extends Controller
{
    public function index()
    {
        $page = 'profile';

        $member = MemberHelper::findByNik(session('NIK_Member'));

        if ($member) {
            session(['Id_Member' => $member->Id_Member]);
            session(['Name_Member' => $member->Name_Member]);
        }

        return view('members.profile.index', compact('page', 'member'));
    }

    public function update(Request $request, string $Id_Member)
    {
        if (MemberHelper::useRifa()) {
            return redirect()->route('profile_member')->withErrors(['error' => 'Cannot modify profile when using RIFA integration.']);
        }

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
