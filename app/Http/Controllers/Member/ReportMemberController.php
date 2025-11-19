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
            ->orderBy('Start_Report', 'desc')
            ->with('member')
            ->get();
        $member = Member::find($Id_Member);

        return view('members.reports.index', compact('page', 'reports', 'member'));
    }

    public function report_list_member($Id_Report){
        $page = "report";

        $report = Report::findOrFail($Id_Report);
        $list_reports = List_Report::where('Id_Report', $Id_Report)->orderBy('Name_Procedure', 'asc')->get();
        $Id_Member = session('Id_Member');
        $member = Member::find($Id_Member);

        return view('members.reports.list_report', compact('page', 'report', 'list_reports', 'member'));
    }

    public function detail($Id_List_Report)
    {
        $page = "report";

        $Id_Member = session('Id_Member');
        $member = Member::find($Id_Member);

        $listReport = List_Report::with('report')->findOrFail($Id_List_Report);

        $id_member = $listReport->report->member->Id_Member;
        $timeReport = Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');

        $fullPath = 'storage/reports/' . $timeReport . '_' . $id_member;

        $fileName = $listReport->Name_Procedure . '.pdf';
        $pdfPath = $fullPath . '/' . $fileName;

        return view('members.reports.report', compact('page', 'listReport', 'pdfPath', 'member'));
    }

    // public function submit_report(Request $request, $Id_List_Report)
    // {
    //     $listReport = List_Report::with('report')->findOrFail($Id_List_Report);

    //     $id_member = $listReport->report->member->Id_Member;
    //     $timeReport = Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');

    //     if ($request->hasFile('pdf')) {
    //         $pdf = $request->file('pdf');

    //         // Path target di public/storage/reports/...
    //         $path = 'storage/reports/' . $timeReport . '_' . $id_member;
    //         $filename = $listReport->Name_Procedure . '.pdf';

    //         // Pastikan direktori ada
    //         $fullPath = public_path($path);
    //         if (!file_exists($fullPath)) {
    //             mkdir($fullPath, 0755, true);
    //         }

    //         // Pindahkan file ke public/storage/reports/...
    //         $pdf->move($fullPath, $filename);

    //         // Update waktu
    //         $listReport->Time_List_Report = $request->input('timestamp');
    //         $listReport->save();

    //         return response()->json(['success' => true]);
    //     }

    //     return response()->json(['success' => false], 400);
    // }

    public function submit_report(Request $request, $Id_List_Report)
    {
        $listReport = List_Report::with('report.member')->findOrFail($Id_List_Report);

        if (!$request->hasFile('pdf')) {
            return response()->json(['success' => false], 400);
        }

        $id_member = $listReport->report->member->Id_Member;
        $timeReport = \Carbon\Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');
        $fileName = $listReport->Name_Procedure . '.pdf';

        $request->file('pdf')->storeAs("reports/{$timeReport}_{$id_member}", $fileName, 'public');

        $listReport->Time_List_Report = $request->input('timestamp');
        $listReport->save();

        return response()->json(['success' => true]);
    }

    public function uploadPhotos(Request $request, $Id_List_Report)
    {
        $listReport = List_Report::with('report.member')->findOrFail($Id_List_Report);

        if (!$request->hasFile('pdf')) {
            return response()->json(['success' => false, 'message' => 'No PDF received.']);
        }

        $id_member = $listReport->report->member->Id_Member;
        $timeReport = \Carbon\Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');
        $folderName = "{$timeReport}_{$id_member}";
        $fileName = $listReport->Name_Procedure . '.pdf';

        // Simpan PDF langsung ke folder report (timpa)
        $request->file('pdf')->storeAs("reports/{$folderName}", $fileName, 'public');

        return response()->json(['success' => true]);
    }
}
