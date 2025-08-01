<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Team;
use App\Models\User;
use App\Models\Procedure;
use App\Models\Member;
use App\Models\Process;
use App\Models\Report;
use App\Models\List_Report;

class ReportController extends Controller
{
    public function index(){
        $page = "report";

        $teams = Team::all();

        return view('leaders.reports.index', compact('page', 'teams'));
    }

    public function process(string $Id_Team){
        $page = "report";

        $Id_Team = $Id_Team;

        $team = Team::where('Id_Team', $Id_Team)->first();
        $processes = Process::where('Id_Team', $Id_Team)->get();

        return view('leaders.reports.process', compact('page', 'team', 'processes'));
    }

    public function create_process(Request $request)
    {
        $request->validate([
            'Id_Team' => 'required|string',
            'Name_Process' => 'required|string'
        ]);

        $timestamp = now()->format('Y-m-d_H-i-s');

        // Ambil Name_Team berdasarkan Id_Team
        $team = Team::find($request->Id_Team);
        if (!$team) {
            return back()->withErrors(['Team tidak ditemukan']);
        }
        $nameTeam = $team->Name_Team;

        $teamPath = 'reports/' . $nameTeam;
        if (!Storage::disk('public')->exists($teamPath)) {
            Storage::disk('public')->makeDirectory($teamPath);
        }

        $processPath = $teamPath . '/' . $request->Name_Process;
        if (!Storage::disk('public')->exists($processPath)) {
            Storage::disk('public')->makeDirectory($processPath);
        }

        // Simpan ke tabel processes
        Process::create([
            'Id_Team' => $request->Id_Team,
            'Name_Process' => $request->Name_Process,
            'Time_Created_Process' => $timestamp,
        ]);

        return redirect()->back()->with('success', 'Process berhasil disimpan.');
    }

    public function reporter(string $Id_Process){
        $page = "report";

        $process = Process::where('Id_Process', $Id_Process)->with('team')->first();
        $reports = Report::where('Id_Process', $Id_Process)->with('member')->get();
        $members = Member::all();

        return view('leaders.reports.reporter', compact('page', 'process', 'reports', 'members'));
    }

    public function create_reporter(Request $request)
    {
        $request->validate([
            'Id_Process' => 'required|string',
            'Name_Team' => 'required|string',
            'Name_Process' => 'required|string',
            'Id_Member' => 'required|array'
        ]);

        $timestamp = now()->format('Y-m-d_H-i-s');

        $teamPath = 'reports/' . $request->Name_Team;
        $processPath = $teamPath . '/' . $request->Name_Process;

        foreach ($request->Id_Member as $id_member) {
            // Buat folder dasar untuk member
            $fullPath = $processPath . '/' . $id_member . '_' . $timestamp;
            if (!Storage::disk('public')->exists($fullPath)) {
                Storage::disk('public')->makeDirectory($fullPath);
            }

            // Simpan ke tabel reports
            Report::create([
                'Id_Process' => $request->Id_Process,
                'Id_Member' => $id_member,
                'Time_Created_Report' => $timestamp,
            ]);
        }

        return redirect()->back()->with('success', 'Reporter berhasil disimpan.');
    }

    public function list_report(string $Id_Report){
        $page = "report";

        $report = Report::where('Id_Report', $Id_Report)->with('process')->with('member')->first();
        $list_reports = List_Report::where('Id_Report', $Id_Report)->with('report')->orderBy('Name_Procedure')->get();

        $usedProcedures = $list_reports->pluck('Name_Procedure')->toArray();
        $procedures = Procedure::where('Name_Area', $report->process->team->Name_Team)
            ->whereNotIn('Name_Procedure', $usedProcedures)
            ->orderBy('Name_Procedure')
            ->get(['Name_Procedure']);

        return view('leaders.reports.list_report', compact('page', 'report', 'list_reports', 'procedures'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Id_Report' => 'required|string',
            'Name_Procedure' => 'required|array',
        ]);

        $report = Report::where('Id_Report', $request->Id_Report)->with('process')->with('member')->first();
        $procedures = Procedure::whereIn('Name_Procedure', $request->Name_Procedure)->get();

        $nameTeam = $report->process->team->Name_Team ?? 'Unknown';
        $nameProcess = $report->process->Name_Process ?? 'Unknown';
        $name_member = $report->member->Name_Member ?? 'Unknown';
        $id_member = $report->member->Id_Member;
        $timeReport = Carbon::parse($report->Time_Created_Report)->format('Y-m-d_H-i-s');

        $teamPath = 'reports/' . $nameTeam;
        $processPath = $teamPath . '/' . $nameProcess;
        $fullPath = $processPath . '/' . $id_member . '_' . $timeReport;

        if ($procedures->count() > 0) {
            // Data list_reports untuk batch insert
            $data = [];

            foreach ($procedures as $procedure) {
                $parts = explode(' - ', $procedure->Name_Procedure);
                $nameTractor = $parts[1] ?? $procedure->Name_Procedure;

                // Tambahkan ke array untuk insert
                $data[] = [
                    'Id_Report' => $report->Id_Report,
                    'Name_Procedure' => $procedure->Name_Procedure,
                    'Name_Area' => $nameTeam,
                    'Name_Tractor' => $nameTractor,
                    'Item_Procedure' => $procedure->Item_Procedure,
                    'Time_List_Report' => null,
                    'Time_Approved_Leader' => null,
                    'Time_Approved_Auditor' => null,
                    'Reporter_Name' => $name_member,
                    'Leader_Name' => null,
                    'Auditor_Name' => null,
                ];

                // Path asal file
                $sourcePath = 'procedures/' . $nameTractor . '/' . $nameTeam . '/' . $procedure->Name_Procedure . '.pdf';

                // Path tujuan
                $targetName = $procedure->Name_Procedure . '.pdf';
                $targetPath = $fullPath . '/' . $targetName;

                // Copy dan rename jika file asal ada
                if (Storage::disk('public')->exists($sourcePath)) {
                    Storage::disk('public')->copy($sourcePath, $targetPath);
                }
            }

            // Insert ke list_reports
            List_Report::insert($data);
        }

        return redirect()->back()->with('success', 'Report berhasil disimpan.');
    }

    public function report($Id_List_Report)
    {
        $page = "report";

        $Id_User = session('Id_User');
        $user = User::where('Id_User', $Id_User)->first();

        $listReport = List_Report::with('report')->findOrFail($Id_List_Report);

        $nameTeam = $listReport->report->process->team->Name_Team ?? 'Unknown';
        $nameProcess = $listReport->report->process->Name_Process ?? 'Unknown';
        $id_member = $listReport->report->member->Id_Member;
        $timeReport = Carbon::parse($listReport->report->Time_Created_Report)->format('Y-m-d_H-i-s');

        $teamPath = 'storage/reports/' . $nameTeam;
        $processPath = $teamPath . '/' . $nameProcess;
        $fullPath = $processPath . '/' . $id_member . '_' . $timeReport;

        $fileName = $listReport->Name_Procedure . '.pdf';
        $pdfPath = $fullPath . '/' . $fileName;

        return view('leaders.reports.report', compact('page', 'listReport', 'pdfPath', 'user'));
    }

    public function submit_report(Request $request, $Id_List_Report)
    {
        $listReport = List_Report::with('report')->findOrFail($Id_List_Report);

        $nameTeam = $listReport->report->process->team->Name_Team ?? 'Unknown';
        $nameProcess = $listReport->report->process->Name_Process ?? 'Unknown';
        $id_member = $listReport->report->member->Id_Member;
        $timeReport = Carbon::parse($listReport->report->Time_Created_Report)->format('Y-m-d_H-i-s');

        if ($request->hasFile('pdf')) {
            $pdf = $request->file('pdf');

            // Path target di public/storage/reports/...
            $path = 'storage/reports/' . $nameTeam . '/' . $nameProcess . '/' . $id_member . '_' . $timeReport;
            $filename = $listReport->Name_Procedure . '.pdf';

            // Pastikan direktori ada
            $fullPath = public_path($path);
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            // Pindahkan file ke public/storage/reports/...
            $pdf->move($fullPath, $filename);

            // Update waktu
            $listReport->Time_Approved_Leader = $request->input('timestamp');
            $listReport->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }
}
