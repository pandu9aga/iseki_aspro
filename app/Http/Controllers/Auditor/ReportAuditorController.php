<?php

namespace App\Http\Controllers\Auditor;

use App\Http\Controllers\Controller;
use App\Models\List_Report;
use App\Models\Member;
use App\Models\Procedure;
use App\Models\Report;
use App\Models\Tractor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportAuditorController extends Controller
{
    public function index()
    {
        $page = 'report';

        return view('auditors.reports.index', compact('page'));
    }

    public function reporter($year, $month)
    {
        $page = 'report';

        $reports = Report::whereYear('Start_Report', $year)
            ->whereMonth('Start_Report', $month)
            ->orderBy('Start_Report')
            ->with('member')
            ->get();

        $members = Member::all();

        return view('auditors.reports.reporter', compact('page', 'reports', 'members', 'year', 'month'));
    }

    public function list_report(string $Id_Report)
    {
        $page = 'report';

        $report = Report::where('Id_Report', $Id_Report)->with('member')->first();
        $tractors = Tractor::select('Name_Tractor', 'Photo_Tractor')
            ->distinct()
            ->orderBy('Name_Tractor')
            ->get();

        $tractorReports = [];

        foreach ($tractors as $tractor) {
            $count = List_Report::where('Id_Report', $Id_Report)->where('Name_Tractor', $tractor->Name_Tractor)->count();
            $tractorReports[] = [
                'Name_Tractor' => $tractor->Name_Tractor,
                'Photo_Tractor' => $tractor->Photo_Tractor,
                'Report_Count' => $count,
            ];
        }

        return view('auditors.reports.list_report', compact('page', 'report', 'tractorReports', 'Id_Report'));
    }

    public function list_report_detail(string $Id_Report, string $Name_Tractor)
    {
        $page = 'report';

        $report = Report::where('Id_Report', $Id_Report)->with('member')->first();
        $list_reports = List_Report::where('Id_Report', $Id_Report)->where('Name_Tractor', $Name_Tractor)->with('report')->orderBy('Name_Procedure')->get();

        $tractor = Tractor::where('Name_Tractor', $Name_Tractor)->first();

        $usedProcedures = $list_reports->pluck('Name_Procedure')->toArray();
        $procedures = Procedure::whereNotIn('Name_Procedure', $usedProcedures)
            ->where('Name_Tractor', $Name_Tractor)
            ->orderBy('Name_Procedure')
            ->get(['Name_Procedure']);

        return view('auditors.reports.list_report_detail', compact('page', 'report', 'list_reports', 'procedures', 'Id_Report', 'tractor'));
    }

    public function report($Id_List_Report)
    {
        $page = 'report';

        $Id_User = session('Id_User');
        $user = User::where('Id_User', $Id_User)->first();

        $listReport = List_Report::with('report')->findOrFail($Id_List_Report);

        $id_member = $listReport->report->member->id;
        $timeReport = Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');

        $fullPath = 'storage/reports/'.$timeReport.'_'.$id_member;

        $fileName = $listReport->Name_Procedure.'.pdf';
        $pdfPath = $fullPath.'/'.$fileName;

        return view('auditors.reports.report', compact('page', 'listReport', 'pdfPath', 'user'));
    }

    public function submit_report(Request $request, $Id_List_Report)
    {
        $listReport = List_Report::with('report')->findOrFail($Id_List_Report);

        $id_member = $listReport->report->member->id;
        $timeReport = Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');

        if ($request->hasFile('pdf')) {
            $pdf = $request->file('pdf');

            // Path target di public/storage/reports/...
            $path = 'storage/reports/'.$timeReport.'_'.$id_member;
            $filename = $listReport->Name_Procedure.'.pdf';

            // Pastikan direktori ada
            $fullPath = public_path($path);
            if (! file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            // Pindahkan file ke public/storage/reports/...
            $pdf->move($fullPath, $filename);

            // Update waktu
            $listReport->Time_Approved_Auditor = $request->input('timestamp');
            $listReport->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }

    public function destroy_list_report($Id_List_Report)
    {
        $listReport = List_Report::with('report')->findOrFail($Id_List_Report);

        // Ambil info untuk path folder
        $id_member = $listReport->report->Id_Member;
        $startReport = Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');
        $pdfPath = "reports/{$startReport}_{$id_member}/{$listReport->Name_Procedure}.pdf";

        // Hapus file PDF jika ada
        if (Storage::disk('public')->exists($pdfPath)) {
            Storage::disk('public')->delete($pdfPath);
        }

        // Hapus dari database
        $listReport->delete();

        return redirect()->back()->with('success', 'Prosedur berhasil dihapus dari laporan.');
    }
}
