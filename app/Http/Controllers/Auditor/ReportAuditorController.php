<?php

namespace App\Http\Controllers\Auditor;

use App\Http\Controllers\Controller;
use App\Models\List_Report;
use App\Helpers\MemberHelper;
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
            ->get();

        $reportDate = Carbon::createFromDate($year, $month, 1)->format('Y-m-d');
        $members = MemberHelper::getAllMembers($reportDate);

        return view('auditors.reports.reporter', compact('page', 'reports', 'members', 'year', 'month'));
    }

    public function list_report(string $Id_Report)
    {
        $page = 'report';

        $report = Report::where('Id_Report', $Id_Report)->first();
        if (! $report) {
            return redirect()->back()->withErrors(['error' => 'Report tidak ditemukan.']);
        }

        $tractors = Tractor::select('Name_Tractor', 'Photo_Tractor')
            ->distinct()
            ->orderBy('Name_Tractor')
            ->get();

        $counts = List_Report::where('Id_Report', $Id_Report)
            ->groupBy('Name_Tractor')
            ->selectRaw('Name_Tractor, count(*) as count')
            ->pluck('count', 'Name_Tractor');

        $tractorReports = [];

        foreach ($tractors as $tractor) {
            $tractorReports[] = [
                'Name_Tractor' => $tractor->Name_Tractor,
                'Photo_Tractor' => $tractor->Photo_Tractor,
                'Report_Count' => $counts->get($tractor->Name_Tractor, 0),
            ];
        }

        // Fetch Member & Dates
        $member = $report->member;
        $nik = $member->NIK_Member ?? null;
        $year = Carbon::parse($report->Start_Report)->year;
        $month = Carbon::parse($report->Start_Report)->month;

        // 1. Get Absensis from iseki_rifa
        $absensis = [];
        if ($nik) {
            $emp = \App\Models\Employee::where('nik', $nik)->first();
            if ($emp) {
                $absensis = \Illuminate\Support\Facades\DB::connection('rifa')
                    ->table('absensis')
                    ->where('employee_id', $emp->id)
                    ->whereYear('tanggal', $year)
                    ->whereMonth('tanggal', $month)
                    ->orderBy('tanggal', 'asc')
                    ->get();
            }
        }

        return view('auditors.reports.list_report', compact('page', 'report', 'tractorReports', 'Id_Report', 'absensis'));
    }

    public function list_report_daily(string $Id_Report, string $date)
    {
        $page = 'report';

        $report = \App\Models\Report::where('Id_Report', $Id_Report)->first();
        if (! $report) {
            abort(404);
        }

        $member = $report->member;
        $nik = $member->NIK_Member ?? null;
        $startReportDate = Carbon::parse($report->Start_Report)->format('Y-m-d');
        $targetDate = Carbon::parse($date)->format('Y-m-d');
        $targetDateCompact = Carbon::parse($date)->format('Ymd');

        // Hanya akun leader bernama Saiful yang boleh menyalin jobdesc pengganti
        $canCopyJobdesc = false;
        if (session('Id_Type_User') == 2) {
            $loginUser = \App\Models\User::where('Id_User', session('Id_User'))->first();
            $canCopyJobdesc = $loginUser && strtolower($loginUser->Name_User) === 'saiful';
        }

        // Helper rule mapping Type_Plan to Name_Tractor
        $mapTypePlanToTractors = function ($typePlan) {
            $typePlan = trim((string) $typePlan);
            $map = [
                'GC' => ['MF1GC'],
                'GNT' => ['GNT 1640'],
                'GNTDAI' => ['MF 1650'],
                'MF' => ['MF1E25', 'MF1E35,40'],
                'MFDAI' => ['MF2E'],
                'MFE' => ['MF 1741'],
                'MFEDAI' => ['MF 1756'],
                'NT' => ['NT'],
                'NTDAI' => ['NT DAI'],
                'SF2' => ['SF 2'],
                'SF2CL' => ['SF 2'],
                'SF2MW' => ['SF 2'],
                'SF2日本' => ['SF 2'],
                'SF2CL日本' => ['SF 2'],
                'SF2MW日本' => ['SF 2'],
                'SF5' => ['SF 2'],
                'SUSXG2' => ['SUSXG2'],
                'SXG2' => ['SXG 2'],
                'SXG2CL' => ['SXG 2'],
                'SXG2MW' => ['SXG 2'],
                'SXG2日本' => ['SXG 2'],
                'SXG2CL日本' => ['SF 2'],
                'SXG2MW日本' => ['SXG 2'],
                'SXG3' => ['SXG3'],
                'SXG3CL' => ['SXG3'],
                'SXG3MW' => ['SXG3'],
                'SXG3日本' => ['SXG3'],
                'SXG3CL日本' => ['SXG3'],
                'SXG3MW日本' => ['SXG3'],
                'TLE' => ['TLE'],
                'TLEDAI' => ['TLE DAI'],
                'TXGS' => ['TXGS EROPA', 'TXGS JAPAN'],
            ];

            return $map[$typePlan] ?? [];
        };

        // Get Daily Jobs & Replacements for the clicked date only
        $dailyJobsData = [];
        if ($nik) {
            $dailyJobs = \Illuminate\Support\Facades\DB::select(
                "SELECT * FROM iseki_efficiency.daily_jobs WHERE Nik_Daily_Job = ? AND Production_Date_Plan = ? ORDER BY Sequence_No_Plan ASC",
                [$nik, $targetDateCompact]
            );

            foreach ($dailyJobs as $dj) {
                // Get replacement if any
                $replacements = \Illuminate\Support\Facades\DB::select(
                    "SELECT * FROM iseki_efficiency.replacements WHERE Id_Daily_Job = ?",
                    [$dj->Id_Daily_Job]
                );

                $repDetails = [];
                foreach ($replacements as $rep) {
                    $repNik = $rep->NIK_Replacement;
                    $repEmp = \App\Helpers\MemberHelper::findByNik($repNik, $startReportDate);
                    $repName = $repEmp->Name_Member ?? $repNik;

                    // Lookup Podium plan using sequence and production date from replacements
                    $seqNo = $rep->Sequence_No_Plan ?? $dj->Sequence_No_Plan;
                    if ($seqNo !== null && stripos((string) $seqNo, 'T') === false) {
                        $seqNo = str_pad(trim((string) $seqNo), 5, '0', STR_PAD_LEFT);
                    }
                    $prodDate = $rep->Production_Date_Plan ?? $dj->Production_Date_Plan;

                    $plan = \Illuminate\Support\Facades\DB::selectOne(
                        "SELECT * FROM iseki_podium.plans WHERE Sequence_No_Plan = ? AND (Production_Date_Plan = ? OR Production_No_Plan = ?)",
                        [$seqNo, $prodDate, $prodDate]
                    );

                    $typePlan = $plan->Type_Plan ?? null;
                    $mappedTractors = $typePlan ? $mapTypePlanToTractors($typePlan) : [];

                    // Check if already copied in report_replacements table
                    $copiedRecord = \App\Models\ReportReplacement::where('Id_Report', $report->Id_Report)
                        ->where('NIK_Replacement', $repNik)
                        ->where('Sequence_No_Plan', $seqNo)
                        ->first();

                    $repDetails[] = [
                        'replacement_nik' => $repNik,
                        'replacement_name' => $repName,
                        'sequence_no_plan' => $seqNo,
                        'production_date_plan' => $prodDate,
                        'type_plan' => $typePlan,
                        'mapped_tractors' => $mappedTractors,
                        'is_copied' => $copiedRecord ? true : false,
                        'target_report_id' => $copiedRecord ? $copiedRecord->Id_Report_Target : null,
                        'id_report_replacement' => $copiedRecord ? $copiedRecord->Id_Report_Replacement : null,
                    ];
                }

                $dailyJobsData[] = [
                    'daily_job' => $dj,
                    'replacements' => $repDetails,
                ];
            }
        }

        return view('auditors.reports.daily_report', compact('page', 'report', 'Id_Report', 'dailyJobsData', 'targetDate', 'canCopyJobdesc'));
    }

    public function list_report_replacement(string $Id_Report_Replacement)
    {
        $page = 'report';

        $reportReplacement = \App\Models\ReportReplacement::with(['report', 'listReportReplacements'])->findOrFail($Id_Report_Replacement);
        $report = $reportReplacement->report;
        $repMember = \App\Helpers\MemberHelper::findByNik($reportReplacement->NIK_Replacement, $report->Start_Report);

        $list_reports = $reportReplacement->listReportReplacements;

        return view('auditors.reports.list_report_replacement', compact('page', 'reportReplacement', 'report', 'repMember', 'list_reports'));
    }

    public function replacement_report_detail(string $Id_List_Report_Replacement)
    {
        $page = 'report';

        $user = \App\Models\User::where('Id_User', session('Id_User'))->first();
        $listReport = \App\Models\ListReportReplacement::with('reportReplacement.report')->findOrFail($Id_List_Report_Replacement);

        $idRepHeader = $listReport->Id_Report_Replacement;
        $fullPath = 'storage/report_replacements/'.$idRepHeader;
        $fileName = $listReport->Name_Procedure.'.pdf';
        $pdfPath = $fullPath.'/'.$fileName;

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

        return view('auditors.reports.replacement_report', compact(
            'page', 'listReport', 'pdfPath', 'user',
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

            $listReport->Time_Approved_Auditor = $request->input('timestamp');
            $listReport->Auditor_Name = session('Username_User');
            $listReport->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }

    public function list_report_detail(string $Id_Report, string $Name_Tractor)
    {
        $page = 'report';

        $report = Report::where('Id_Report', $Id_Report)->first();
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

        $listReport = List_Report::with(['report', 'Temuans'])->findOrFail($Id_List_Report);

        $id_member = $listReport->report->member->Id_Member;
        $timeReport = Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');

        $fullPath = 'storage/reports/'.$timeReport.'_'.$id_member;

        $fileName = $listReport->Name_Procedure.'.pdf';
        $pdfPath = $fullPath.'/'.$fileName;

        // Get sibling list reports for prev/next navigation (all tractors)
        $siblingReports = List_Report::where('Id_Report', $listReport->Id_Report)
            ->orderBy('Name_Tractor')
            ->orderBy('Name_Procedure')
            ->pluck('Id_List_Report')
            ->toArray();

        $currentIndex = array_search($Id_List_Report, $siblingReports);
        $prevReportId = ($currentIndex !== false && $currentIndex > 0) ? $siblingReports[$currentIndex - 1] : null;
        $nextReportId = ($currentIndex !== false && $currentIndex < count($siblingReports) - 1) ? $siblingReports[$currentIndex + 1] : null;
        $currentPos = $currentIndex !== false ? $currentIndex + 1 : 0;

        return view('auditors.reports.report', compact(
            'page', 'listReport', 'pdfPath', 'user',
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

            // Update waktu and Auditor Name
            $listReport->Time_Approved_Auditor = $request->input('timestamp');
            $listReport->Auditor_Name = session('Username_User');
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

    public function duplicate_report($Id_List_Report)
    {
        $listReport = List_Report::with(['report'])->findOrFail($Id_List_Report);

        // Find a unique name
        $baseName = preg_replace('/ - Retrain \d+$/', '', $listReport->Name_Procedure);

        $count = List_Report::where('Id_Report', $listReport->Id_Report)
            ->where('Name_Procedure', 'LIKE', $baseName.'%')
            ->count();

        $newName = $baseName.' - Retrain '.$count;

        $newListReport = $listReport->replicate();
        $newListReport->Name_Procedure = $newName;
        $newListReport->Time_List_Report = null;
        $newListReport->Time_Approved_Leader = null;
        $newListReport->Time_Approved_Auditor = null;
        $newListReport->Reporter_Name = null;
        $newListReport->Leader_Name = null;
        $newListReport->Auditor_Name = null;
        $newListReport->save();

        // Copy original blank PDF
        $originalBlankPdf = "procedures/{$listReport->Name_Tractor}/{$listReport->Name_Area}/{$baseName}.pdf";
        $id_member = $listReport->report->Id_Member;
        $timeReport = Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');
        $targetFolder = "reports/{$timeReport}_{$id_member}";
        $destPdf = "{$targetFolder}/{$newName}.pdf";

        if (Storage::disk('public')->exists($originalBlankPdf)) {
            Storage::disk('public')->copy($originalBlankPdf, $destPdf);
        }

        return redirect()->route('list_report_detail_auditor', [
            'Id_Report' => $listReport->Id_Report,
            'Name_Tractor' => $listReport->Name_Tractor,
        ])->with('success', 'Training member berhasil diduplikat.');
    }
}
