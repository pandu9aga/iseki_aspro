<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Member;
use App\Models\Report;
use App\Models\List_Report;

class ReportMemberController extends Controller
{
    public function index()
    {
        $page = "report";

        $Id_Member = session('Id_Member');
        $reports = Report::where('Id_Member', $Id_Member)
            ->orderBy('Time_Created_Report', 'desc')
            ->with('process')
            ->with('member')
            ->get();
        $member = Member::find($Id_Member);

        return view('members.reports.index', compact('page', 'reports', 'member'));
    }

    public function report_list_member($Id_Report){
        $page = "report";

        $list_reports = List_Report::where('Id_Report', $Id_Report)->orderBy('Name_Procedure', 'asc')->get();
        $Id_Member = session('Id_Member');
        $member = Member::find($Id_Member);

        return view('members.reports.list_report', compact('page', 'list_reports', 'member'));
    }

    public function detail($Id_List_Report)
    {
        $page = "report";

        $Id_Member = session('Id_Member');
        $member = Member::find($Id_Member);

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

        return view('members.reports.report', compact('page', 'listReport', 'pdfPath', 'member'));
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
            $listReport->Time_List_Report = $request->input('timestamp');
            $listReport->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }
}
