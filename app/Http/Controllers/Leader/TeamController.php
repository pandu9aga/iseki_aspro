<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Helpers\MemberHelper;
use App\Models\Member;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TeamController extends Controller
{
    public function index()
    {
        $page = 'team';

        $members = MemberHelper::getAllMembers();
        $teams = Team::all();

        return view('leaders.teams.index', compact('page', 'members', 'teams'));
    }

    public function team_data()
    {
        $page = 'team';

        $teams = Team::all();

        return view('leaders.teams.team', compact('page', 'teams'));
    }

    public function team_data_create(Request $request)
    {
        // melakukan validasi data
        $request->validate([
            'Name_Team' => 'required',
        ],
            [
                'Name_Team.required' => 'Nama wajib diisi',
            ]);

        // tambah data team
        DB::table('teams')->insert([
            'Name_Team' => $request->input('Name_Team'),
        ]);

        return redirect()->route('team_data');
    }

    public function team_data_update(Request $request, string $Id_Team)
    {
        // melakukan validasi data
        $request->validate([
            'Name_Team' => 'required',
        ],
            [
                'Name_Team.required' => 'Nama wajib diisi',
            ]);

        // update data team
        DB::table('teams')->where('Id_Team', $Id_Team)->update([
            'Name_Team' => $request->input('Name_Team'),
        ]);

        return redirect()->route('team_data');
    }

    public function team_data_destroy(Team $Id_Team)
    {
        $Id_Team->delete();

        return redirect()->route('team_data')->with('success', 'Data berhasil di hapus');
    }

    public function member_create(Request $request)
    {
        if (MemberHelper::useRifa()) {
            return redirect()->route('team')->withErrors(['error' => 'Cannot modify members when using RIFA integration.']);
        }

        // melakukan validasi data
        $request->validate([
            'NIK_Member' => 'required',
            'Name_Member' => 'required',
        ],
            [
                'NIK_Member.required' => 'NIK wajib diisi',
                'Name_Member.required' => 'Nama wajib diisi',
            ]);

        // Tambah data member
        DB::table('members')->insert([
            'NIK_Member' => $request->input('NIK_Member'),
            'Name_Member' => $request->input('Name_Member'),
        ]);

        return redirect()->route('team');
    }

    public function member_update(Request $request, string $Id_Member)
    {
        if (MemberHelper::useRifa()) {
            return redirect()->route('team')->withErrors(['error' => 'Cannot modify members when using RIFA integration.']);
        }

        // melakukan validasi data
        $request->validate([
            'NIK_Member' => 'required',
            'Name_Member' => 'required',
        ],
            [
                'NIK_Member.required' => 'NIK wajib diisi',
                'Name_Member.required' => 'Nama wajib diisi',
            ]);

        // update data member
        DB::table('members')->where('Id_Member', $Id_Member)->update([
            'NIK_Member' => $request->input('NIK_Member'),
            'Name_Member' => $request->input('Name_Member'),
        ]);

        return redirect()->route('team');
    }

    public function member_destroy(Member $Id_Member)
    {
        if (MemberHelper::useRifa()) {
            return redirect()->route('team')->withErrors(['error' => 'Cannot modify members when using RIFA integration.']);
        }

        $Id_Member->delete();

        return redirect()->route('team')->with('success', 'Data berhasil di hapus');
    }

    public function member_import(Request $request)
    {
        if (MemberHelper::useRifa()) {
            return redirect()->route('team')->withErrors(['error' => 'Cannot modify members when using RIFA integration.']);
        }

        $request->validate([
            'excel' => 'required|file|mimes:xls,xlsx',
        ]);

        $file = $request->file('excel');
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        $imported = 0;
        $skipped = 0;

        foreach ($rows as $index => $row) {
            // Lewati baris pertama jika terlihat seperti header (bukan numeric)
            if ($index === 0 && (! is_numeric($row[0] ?? null) || ! is_numeric($row[1] ?? null))) {
                continue;
            }

            $nik = trim($row[0] ?? '');
            $nama = trim($row[1] ?? '');

            if ($nik === '' || $nama === '') {
                continue;
            }

            // Lewati jika NIK sudah ada
            $exists = DB::table('members')->where('NIK_Member', $nik)->exists();
            if ($exists) {
                $skipped++;
                continue;
            }

            DB::table('members')->insert([
                'NIK_Member' => $nik,
                'Name_Member' => $nama,
            ]);
            $imported++;
        }

        $msg = "Berhasil import {$imported} member.";
        if ($skipped > 0) {
            $msg .= " {$skipped} NIK duplikat dilewati.";
        }

        return redirect()->route('team')->with('success', $msg);
    }
}
