<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Tractor;
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

        return view('leaders.reports.index', compact('page'));
    }

    public function reporter($year, $month)
    {
        $page = "report";

        $reports = Report::whereYear('Start_Report', $year)
            ->whereMonth('Start_Report', $month)
            ->orderBy('Start_Report')
            ->with('member')
            ->get();

        $members = Member::all();

        return view('leaders.reports.reporter', compact('page', 'reports', 'members', 'year', 'month'));
    }

    public function create_reporter(Request $request)
    {
        $request->validate([
            'Id_Member' => 'required|array',
            'Start_Report' => 'required|date',
        ]);

        // Ambil hanya tanggalnya saja (tanpa jam)
        $startReportDate = date('Y-m-d', strtotime($request->Start_Report));

        foreach ($request->Id_Member as $id_member) {
            // Cek apakah kombinasi Id_Member dan tanggal yang sama sudah ada
            $exists = Report::where('Id_Member', $id_member)
                ->whereDate('Start_Report', $startReportDate)
                ->exists();

            if ($exists) {
                continue; // Lewati jika sudah ada
            }

            // Buat folder dasar untuk member
            $folderName = $startReportDate . '_' . $id_member;
            $fullPath = 'reports/' . $folderName;
            if (!Storage::disk('public')->exists($fullPath)) {
                Storage::disk('public')->makeDirectory($fullPath);
            }

            // Simpan ke tabel reports
            Report::create([
                'Id_Member' => $id_member,
                'Start_Report' => $startReportDate, // hanya tanggal
            ]);
        }

        return redirect()->back()->with('success', 'Reporter berhasil disimpan.');
    }

    public function list_report(string $Id_Report){
        $page = "report";

        $report = Report::where('Id_Report', $Id_Report)->with('member')->first();
        $tractors = Tractor::select('Name_Tractor', 'Photo_Tractor')
            ->distinct()
            ->get();

        $tractorReports = [];

        foreach ($tractors as $tractor) {
            $count = List_Report::where('Name_Tractor', $tractor->Name_Tractor)->count();
            $tractorReports[] = [
                'Name_Tractor' => $tractor->Name_Tractor,
                'Photo_Tractor' => $tractor->Photo_Tractor,
                'Report_Count' => $count,
            ];
        }

        return view('leaders.reports.list_report', compact('page', 'report', 'tractorReports', 'Id_Report'));
    }

    public function list_report_detail(string $Id_Report, string $Name_Tractor){
        $page = "report";

        $report = Report::where('Id_Report', $Id_Report)->with('member')->first();
        $list_reports = List_Report::where('Id_Report', $Id_Report)->with('report')->orderBy('Name_Procedure')->get();

        $tractor = Tractor::where('Name_Tractor', $Name_Tractor)->first();

        $usedProcedures = $list_reports->pluck('Name_Procedure')->toArray();
        $procedures = Procedure::whereNotIn('Name_Procedure', $usedProcedures)
            ->where('Name_Tractor', $Name_Tractor)
            ->orderBy('Name_Procedure')
            ->get(['Name_Procedure']);

        return view('leaders.reports.list_report_detail', compact('page', 'report', 'list_reports', 'procedures', 'Id_Report', 'tractor'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Id_Report' => 'required|string',
            'Name_Procedure' => 'required|array',
        ]);

        $report = Report::where('Id_Report', $request->Id_Report)->with('member')->first();
        $procedures = Procedure::whereIn('Name_Procedure', $request->Name_Procedure)->get();

        $name_member = $report->member->Name_Member ?? 'Unknown';
        $id_member = $report->member->Id_Member;
        $timeReport = Carbon::parse($report->Start_Report)->format('Y-m-d');

        $fullPath = 'reports/' . $timeReport . '_' . $id_member;

        if ($procedures->count() > 0) {
            // Data list_reports untuk batch insert
            $data = [];

            foreach ($procedures as $procedure) {
                $nameArea = $procedure->Name_Area;
                $nameTractor = $procedure->Name_Tractor;

                // Tambahkan ke array untuk insert
                $data[] = [
                    'Id_Report' => $report->Id_Report,
                    'Name_Procedure' => $procedure->Name_Procedure,
                    'Name_Area' => $nameArea,
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
                $sourcePath = 'procedures/' . $nameTractor . '/' . $nameArea . '/' . $procedure->Name_Procedure . '.pdf';

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

        $id_member = $listReport->report->member->Id_Member;
        $timeReport = Carbon::parse($listReport->report->Time_Created_Report)->format('Y-m-d');

        $fullPath = 'storage/reports/' . $timeReport . '_' . $id_member;

        $fileName = $listReport->Name_Procedure . '.pdf';
        $pdfPath = $fullPath . '/' . $fileName;

        return view('leaders.reports.report', compact('page', 'listReport', 'pdfPath', 'user'));
    }

    public function submit_report(Request $request, $Id_List_Report)
    {
        $listReport = List_Report::with('report')->findOrFail($Id_List_Report);

        $id_member = $listReport->report->member->Id_Member;
        $timeReport = Carbon::parse($listReport->report->Time_Created_Report)->format('Y-m-d');

        if ($request->hasFile('pdf')) {
            $pdf = $request->file('pdf');

            // Path target di public/storage/reports/...
            $path = 'storage/reports/' . $timeReport . '_' . $id_member;
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
