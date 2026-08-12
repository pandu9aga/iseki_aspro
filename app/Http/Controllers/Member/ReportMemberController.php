<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\List_Report;
use App\Helpers\MemberHelper;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportMemberController extends Controller
{
    public function index()
    {
        $page = 'report';

        $nik = session('NIK_Member');
        $memberIds = MemberHelper::getLinkedIds($nik);
        $reports = Report::whereIn('Id_Member', $memberIds)
            ->orderBy('Start_Report', 'desc')
            ->get();
        $member = MemberHelper::findByNik(session('NIK_Member'));

        return view('members.reports.index', compact('page', 'reports', 'member'));
    }

    public function report_list_member($Id_Report)
    {
        $page = 'report';

        $report = Report::findOrFail($Id_Report);
        $list_reports = List_Report::where('Id_Report', $Id_Report)->orderBy('Name_Procedure', 'asc')->get();
        $member = MemberHelper::findByNik(session('NIK_Member'));

        return view('members.reports.list_report', compact('page', 'report', 'list_reports', 'member'));
    }

    public function detail($Id_List_Report)
    {
        $page = 'report';

        $member = MemberHelper::findByNik(session('NIK_Member'));

        $listReport = List_Report::with('report')->findOrFail($Id_List_Report);

        $id_member = $listReport->report->member->Id_Member;
        $timeReport = Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');

        $fullPath = 'storage/reports/'.$timeReport.'_'.$id_member;

        $fileName = $listReport->Name_Procedure.'.pdf';
        $pdfPath = $fullPath.'/'.$fileName;

        // Get sibling list reports for prev/next navigation (all procedures in the report)
        $siblingReports = List_Report::where('Id_Report', $listReport->Id_Report)
            ->orderBy('Name_Tractor')
            ->orderBy('Name_Procedure')
            ->pluck('Id_List_Report')
            ->toArray();

        $currentIndex = array_search($Id_List_Report, $siblingReports);
        $prevReportId = ($currentIndex !== false && $currentIndex > 0) ? $siblingReports[$currentIndex - 1] : null;
        $nextReportId = ($currentIndex !== false && $currentIndex < count($siblingReports) - 1) ? $siblingReports[$currentIndex + 1] : null;
        $currentPos = $currentIndex !== false ? $currentIndex + 1 : 0;

        return view('members.reports.report', compact(
            'page', 'listReport', 'pdfPath', 'member',
            'prevReportId', 'nextReportId', 
            'currentPos', 'siblingReports'
        ));
    }

    public function submit_report(Request $request, $Id_List_Report)
    {
        $listReport = List_Report::with('report')->findOrFail($Id_List_Report);

        $id_member = $listReport->report->member->Id_Member;
        $timeReport = Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');

        if ($request->hasFile('pdf')) {
            $request->validate([
                'pdf' => 'required|file|mimes:pdf|max:20480',
            ]);

            $path = 'reports/' . $timeReport . '_' . $id_member;
            $filename = $listReport->Name_Procedure . '.pdf';
            $targetPath = $path . '/' . $filename;

            if (! Storage::disk('public')->exists($path)) {
                Storage::disk('public')->makeDirectory($path);
            }

            Storage::disk('public')->put($targetPath, file_get_contents($request->file('pdf')->getRealPath()));

            // Update waktu
            $listReport->Time_List_Report = $request->input('timestamp');
            $listReport->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }

    public function replacement_index()
    {
        $page = 'replacement';

        $nik = session('NIK_Member');
        $replacements = \App\Models\ReportReplacement::with('report')
            ->where('NIK_Replacement', $nik)
            ->orderBy('id', 'desc')
            ->get();

        $member = MemberHelper::findByNik($nik);

        return view('members.replacements.index', compact('page', 'replacements', 'member'));
    }

    public function replacement_list($Id_Report_Replacement)
    {
        $page = 'replacement';

        $reportReplacement = \App\Models\ReportReplacement::with(['report', 'listReportReplacements'])->findOrFail($Id_Report_Replacement);
        $list_reports = $reportReplacement->listReportReplacements;
        $member = MemberHelper::findByNik(session('NIK_Member'));

        return view('members.replacements.list_report', compact('page', 'reportReplacement', 'list_reports', 'member'));
    }

    public function replacement_detail($Id_List_Report_Replacement)
    {
        $page = 'replacement';

        $member = MemberHelper::findByNik(session('NIK_Member'));
        $listReport = \App\Models\ListReportReplacement::with('reportReplacement.report')->findOrFail($Id_List_Report_Replacement);

        $idRepHeader = $listReport->Id_Report_Replacement;
        $fullPath = 'storage/report_replacements/'.$idRepHeader;
        $fileName = $listReport->Name_Procedure.'.pdf';
        $pdfPath = $fullPath.'/'.$fileName;

        // Check if file exists, if not fallback to template procedure
        if (! Storage::disk('public')->exists('report_replacements/'.$idRepHeader.'/'.$fileName)) {
            $pdfPath = 'storage/procedures/'.$listReport->Name_Tractor.'/'.$listReport->Name_Area.'/'.$fileName;
        }

        $siblingReports = \App\Models\ListReportReplacement::where('Id_Report_Replacement', $idRepHeader)
            ->orderBy('Name_Tractor')
            ->orderBy('Name_Procedure')
            ->pluck('Id_List_Report_Replacement')
            ->toArray();

        $currentIndex = array_search($Id_List_Report_Replacement, $siblingReports);
        $prevReportId = ($currentIndex !== false && $currentIndex > 0) ? $siblingReports[$currentIndex - 1] : null;
        $nextReportId = ($currentIndex !== false && $currentIndex < count($siblingReports) - 1) ? $siblingReports[$currentIndex + 1] : null;
        $currentPos = $currentIndex !== false ? $currentIndex + 1 : 0;

        return view('members.replacements.report', compact(
            'page', 'listReport', 'pdfPath', 'member',
            'prevReportId', 'nextReportId',
            'currentPos', 'siblingReports'
        ));
    }

    public function submit_replacement_report(Request $request, $Id_List_Report_Replacement)
    {
        $listReport = \App\Models\ListReportReplacement::with('reportReplacement')->findOrFail($Id_List_Report_Replacement);

        $idRepHeader = $listReport->Id_Report_Replacement;

        if ($request->hasFile('pdf')) {
            $request->validate([
                'pdf' => 'required|file|mimes:pdf|max:20480',
            ]);

            $path = 'report_replacements/' . $idRepHeader;
            $filename = $listReport->Name_Procedure . '.pdf';
            $targetPath = $path . '/' . $filename;

            if (! Storage::disk('public')->exists($path)) {
                Storage::disk('public')->makeDirectory($path);
            }

            Storage::disk('public')->put($targetPath, file_get_contents($request->file('pdf')->getRealPath()));

            // Update timestamp
            $listReport->Time_List_Report = $request->input('timestamp');
            $listReport->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }
}
