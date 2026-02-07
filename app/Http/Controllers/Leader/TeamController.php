<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\Team;
use App\Models\Member;

class TeamController extends Controller
{
    public function index(){
        $page = "team";

        $members = Member::all();
        $teams = Team::all();
        return view('leaders.teams.index', compact('page', 'members', 'teams'));
    }

    public function team_data(){
        $page = "team";

        $teams = Team::all();
        return view('leaders.teams.team', compact('page', 'teams'));
    }

    public function team_data_create(Request $request)
    {
        // melakukan validasi data
        $request->validate([
            'Name_Team' => 'required'
        ],
        [
            'Name_Team.required' => 'Nama wajib diisi'
        ]);
        
        //tambah data team
        DB::table('teams')->insert([
            'Name_Team' => $request->input('Name_Team')
        ]);
        
        return redirect()->route('team_data');
    }
    
    public function team_data_update(Request $request, string $Id_Team)
    {
        // melakukan validasi data
        $request->validate([
            'Name_Team' => 'required'
        ],
        [
            'Name_Team.required' => 'Nama wajib diisi'
        ]);
    
        //update data team
        DB::table('teams')->where('Id_Team',$Id_Team)->update([
            'Name_Team' => $request->input('Name_Team')
        ]);
                
        return redirect()->route('team_data');
    }

    public function team_data_destroy(Team $Id_Team)
    {
        $Id_Team->delete();
        
        return redirect()->route('team_data')->with('success','Data berhasil di hapus' );
    }

    public function member_create(Request $request)
    {
        // melakukan validasi data
        $request->validate([
            'NIK_Member' => 'required',
            'Name_Member' => 'required'
        ],
        [
            'NIK_Member.required' => 'NIK wajib diisi',
            'Name_Member.required' => 'Nama wajib diisi'
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
        // melakukan validasi data
        $request->validate([
            'NIK_Member' => 'required',
            'Name_Member' => 'required'
        ],
        [
            'NIK_Member.required' => 'NIK wajib diisi',
            'Name_Member.required' => 'Nama wajib diisi'
        ]);
    
        //update data member
        DB::table('members')->where('Id_Member',$Id_Member)->update([
            'NIK_Member' => $request->input('NIK_Member'),
            'Name_Member' => $request->input('Name_Member'),
        ]);
                
        return redirect()->route('team');
    }

    public function member_destroy(Member $Id_Member)
    {
        $Id_Member->delete();
        
        return redirect()->route('team')->with('success','Data berhasil di hapus' );
    }

    public function member_import(Request $request)
    {
        $request->validate([
            'excel' => 'required|file|mimes:xls,xlsx'
        ]);

        $file = $request->file('excel');
        $spreadsheet = IOFactory::load($file->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray();

        foreach ($rows as $row) {
            $nik = $row[0];
            $nama = $row[1];

            if ($nik && $nama) {
                DB::table('members')->insert([
                    'NIK_Member' => $nik,
                    'Name_Member' => $nama,
                ]);
            }
        }

        return redirect()->route('team')->with('success', 'Data berhasil diimport.');
    }
}
