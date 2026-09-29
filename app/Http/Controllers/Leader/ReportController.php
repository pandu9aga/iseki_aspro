<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Helpers\MemberHelper;
use App\Models\Employee;
use App\Models\List_Report;
use App\Models\Member;
use App\Models\Procedure;
use App\Models\Report;
use App\Models\Tractor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    public function index()
    {
        $page = 'report';

        return view('leaders.reports.index', compact('page'));
    }

    public function reporter($year, $month)
    {
        $page = 'report';

        $reports = Report::whereYear('Start_Report', $year)
            ->whereMonth('Start_Report', $month)
            ->orderBy('Start_Report')
            ->get();

        // Tentukan sumber data member berdasarkan bulan report, bukan tanggal hari ini
        $reportDate = Carbon::createFromDate($year, $month, 1)->format('Y-m-d');
        $members = MemberHelper::getAllMembers($reportDate);

        // Hanya akun milik Saiful yang dapat melihat & menggunakan fitur re-upload master PDF
        $loginUser = User::where('Id_User', session('Id_User'))->first();
        $isSaiful = $loginUser && (
            strtolower($loginUser->Username_User ?? '') === 'saiful' ||
            strtolower($loginUser->Name_User ?? '') === 'saiful'
        );

        return view('leaders.reports.reporter', compact('page', 'reports', 'members', 'year', 'month', 'isSaiful'));
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
            // Validasi member ada di sumber data yang sesuai dengan tanggal report
            if (! MemberHelper::exists($id_member, $startReportDate)) {
                continue;
            }

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
            if (! Storage::disk('public')->exists($fullPath)) {
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

        // Hitung jumlah prosedur per tractor dalam 1 query (hindari N+1)
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

        return view('leaders.reports.list_report', compact('page', 'report', 'tractorReports', 'Id_Report'));
    }

    public function list_report_absensi(string $Id_Report)
    {
        $page = 'report';

        $report = Report::where('Id_Report', $Id_Report)->first();
        if (! $report) {
            return redirect()->back()->withErrors(['error' => 'Report tidak ditemukan.']);
        }

        // Fetch Member & Dates
        $member = $report->member;
        $nik = $member->NIK_Member ?? null;
        $year = Carbon::parse($report->Start_Report)->year;
        $month = Carbon::parse($report->Start_Report)->month;

        // Get Absensis from iseki_rifa
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

        return view('leaders.reports.list_report_absensi', compact('page', 'report', 'absensis'));
    }

    public function list_report_daily(string $Id_Report, string $date)
    {
        $page = 'report';

        $report = Report::where('Id_Report', $Id_Report)->first();
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

        return view('leaders.reports.daily_report', compact('page', 'report', 'Id_Report', 'dailyJobsData', 'targetDate', 'canCopyJobdesc'));
    }

    public function list_report_replacement(string $Id_Report_Replacement)
    {
        $page = 'report';

        $reportReplacement = \App\Models\ReportReplacement::with(['report', 'listReportReplacements'])->findOrFail($Id_Report_Replacement);
        $report = $reportReplacement->report;
        $repMember = \App\Helpers\MemberHelper::findByNik($reportReplacement->NIK_Replacement, $report->Start_Report);

        $list_reports = $reportReplacement->listReportReplacements;

        $loginUser = User::where('Id_User', session('Id_User'))->first();
        $isSaiful = $loginUser && strtolower($loginUser->Name_User ?? '') === 'saiful';

        return view('leaders.reports.list_report_replacement', compact('page', 'reportReplacement', 'report', 'repMember', 'list_reports', 'isSaiful'));
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

        return view('leaders.reports.replacement_report', compact(
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

            if (session('Id_Type_User') == 2) {
                $listReport->Time_Approved_Leader = $request->input('timestamp');
                $listReport->Leader_Name = session('Username_User');
                $role = 'leader';
            } elseif (session('Id_Type_User') == 1) {
                $listReport->Time_Approved_Auditor = $request->input('timestamp');
                $listReport->Auditor_Name = session('Username_User');
                $role = 'auditor';
            } else {
                $role = 'leader';
            }

            // Simpan snapshot berdasarkan role untuk keperluan partial reset
            Storage::disk('public')->copy($targetPath, $path . '/' . $listReport->Name_Procedure . '.' . $role . '.pdf');

            if ($request->filled('qr_codes')) {
                $listReport->Qr_Codes = \App\Helpers\QrHelper::mergeQrCodes(
                    $listReport->Qr_Codes,
                    $role,
                    $request->input('qr_codes')
                );
            }
            $listReport->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }

    public function copyJobdescReplacement(Request $request)
    {
        // Hanya akun leader bernama Saiful yang boleh menyalin jobdesc pengganti
        $userTypeId = session('Id_Type_User');
        $loginUser = \App\Models\User::where('Id_User', session('Id_User'))->first();
        $isSaiful = $loginUser && $userTypeId == 2 && strtolower($loginUser->Name_User) === 'saiful';
        if (! $isSaiful) {
            return redirect()->back()->withErrors(['error' => 'Hanya leader Saiful yang memiliki akses untuk melakukan copy prosedur pengganti.']);
        }

        $request->validate([
            'Id_Report' => 'required',
            'replacement_nik' => 'required',
            'mapped_tractors' => 'required|array',
        ]);

        $report = Report::where('Id_Report', $request->Id_Report)->first();
        if (! $report) {
            return redirect()->back()->withErrors(['error' => 'Report tidak ditemukan.']);
        }

        $startReportDate = Carbon::parse($report->Start_Report)->format('Y-m-d');

        // Find replacement member target
        $repMember = \App\Helpers\MemberHelper::findByNik($request->replacement_nik, $startReportDate);
        if (! $repMember) {
            return redirect()->back()->withErrors(['error' => 'Member pengganti tidak ditemukan di database.']);
        }

        // (Tanpa pembuatan Report target) Replacement hanya disimpan di report_replacements.

        // Get list_reports from source report that match mapped_tractors
        $sourceListReports = List_Report::where('Id_Report', $report->Id_Report)
            ->whereIn('Name_Tractor', $request->mapped_tractors)
            ->get();

        if ($sourceListReports->isEmpty()) {
            return redirect()->back()->withErrors(['error' => 'Tidak ada prosedur jobdesc pada tractor tersebut untuk disalin.']);
        }

        // Save record into report_replacements table
        $repHeader = \App\Models\ReportReplacement::updateOrCreate([
            'Id_Report' => $report->Id_Report,
            'NIK_Replacement' => $request->replacement_nik,
            'Sequence_No_Plan' => $request->sequence_no_plan ?? null,
        ], [
            'Name_Tractor' => implode(',', $request->mapped_tractors),
            'Production_Date_Plan' => $request->production_date_plan ?? null,
            'Type_Plan' => $request->type_plan ?? null,
            'Id_Report_Target' => null,
        ]);

        // Save into list_report_replacements table for specific replacement tracking
        $repFullPath = 'report_replacements/' . $repHeader->Id_Report_Replacement;
        if (! Storage::disk('public')->exists($repFullPath)) {
            Storage::disk('public')->makeDirectory($repFullPath);
        }

        foreach ($sourceListReports as $slr) {
            \App\Models\ListReportReplacement::updateOrCreate([
                'Id_Report_Replacement' => $repHeader->Id_Report_Replacement,
                'Name_Procedure' => $slr->Name_Procedure,
                'Name_Tractor' => $slr->Name_Tractor,
            ], [
                'Name_Area' => $slr->Name_Area,
                'Item_Procedure' => $slr->Item_Procedure,
                'Reporter_Name' => $repMember->Name_Member,
            ]);

            $sourceFilePath = 'procedures/' . $slr->Name_Tractor . '/' . $slr->Name_Area . '/' . $slr->Name_Procedure . '.pdf';
            $repFilePath = $repFullPath . '/' . $slr->Name_Procedure . '.pdf';
            if (Storage::disk('public')->exists($sourceFilePath) && ! Storage::disk('public')->exists($repFilePath)) {
                Storage::disk('public')->copy($sourceFilePath, $repFilePath);
            }
        }

        return redirect()->back()->with('success', "Berhasil menyalin prosedur ke member pengganti ({$repMember->Name_Member}).");
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

        $loginUser = User::where('Id_User', session('Id_User'))->first();
        $isSaiful = $loginUser && strtolower($loginUser->Name_User ?? '') === 'saiful';

        return view('leaders.reports.list_report_detail', compact('page', 'report', 'list_reports', 'procedures', 'Id_Report', 'tractor', 'isSaiful'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Id_Report' => 'required|string',
            'Name_Procedure' => 'required|array',
        ]);

        $report = Report::where('Id_Report', $request->Id_Report)->first();

        if (! $report || ! $report->member) {
            return redirect()->back()->withErrors(['error' => 'Report atau data member tidak ditemukan.']);
        }

        $procedures = Procedure::whereIn('Name_Procedure', $request->Name_Procedure)->get();

        $member = $report->member;
        $name_member = $member->Name_Member ?? 'Unknown';
        $id_member = $member->Id_Member;
        $timeReport = Carbon::parse($report->Start_Report)->format('Y-m-d');

        $fullPath = 'reports/' . $timeReport . '_' . $id_member;

        if ($procedures->count() > 0) {
            $data = [];

            foreach ($procedures as $procedure) {
                $nameArea = $procedure->Name_Area;
                $nameTractor = $procedure->Name_Tractor;

                // Tambahkan id_member ke Pic_Procedure jika belum ada
                $picProcedure = $procedure->Pic_Procedure ?? [];
                if (! in_array($id_member, $picProcedure)) {
                    $picProcedure[] = $id_member;
                    $procedure->Pic_Procedure = $picProcedure;
                    $procedure->save();
                }

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

                $sourcePath = 'procedures/' . $nameTractor . '/' . $nameArea . '/' . $procedure->Name_Procedure . '.pdf';
                $targetName = $procedure->Name_Procedure . '.pdf';
                $targetPath = $fullPath . '/' . $targetName;

                if (Storage::disk('public')->exists($sourcePath)) {
                    Storage::disk('public')->copy($sourcePath, $targetPath);
                }
            }

            List_Report::insert($data);
        }

        return redirect()->back()->with('success', 'Report berhasil disimpan dan PIC ditambahkan.');
    }

    public function report(Request $request, $Id_List_Report)
    {
        $page = 'report';

        $Id_User = session('Id_User');
        $user = User::where('Id_User', $Id_User)->first();

        $listReport = List_Report::with('report')->findOrFail($Id_List_Report);

        $id_member = $listReport->report->member->Id_Member;
        $timeReport = Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');

        $fullPath = 'storage/reports/' . $timeReport . '_' . $id_member;

        $fileName = $listReport->Name_Procedure . '.pdf';
        $pdfPath = $fullPath . '/' . $fileName;

        $context = $request->query('context');

        if ($context === 'audit') {
            $date = $request->query('date');
            $auditorName = $request->query('auditorName');

            $query = List_Report::whereDate('Time_Approved_Auditor', $date);

            if ($auditorName === 'Unknown Auditor') {
                $query->whereNull('Auditor_Name');
            } else {
                $query->where('Auditor_Name', $auditorName);
            }

            $siblingReports = $query->orderBy('Id_List_Report')->pluck('Id_List_Report')->toArray();
        } else {
            // Get sibling list reports for prev/next navigation
            $siblingReports = List_Report::where('Id_Report', $listReport->Id_Report)
                ->where('Name_Tractor', $listReport->Name_Tractor)
                ->orderBy('Name_Procedure')
                ->pluck('Id_List_Report')
                ->toArray();
        }

        $currentIndex = array_search($Id_List_Report, $siblingReports);
        $prevReportId = ($currentIndex !== false && $currentIndex > 0) ? $siblingReports[$currentIndex - 1] : null;
        $nextReportId = ($currentIndex !== false && $currentIndex < count($siblingReports) - 1) ? $siblingReports[$currentIndex + 1] : null;
        $currentPos = $currentIndex !== false ? $currentIndex + 1 : 0;

        return view('leaders.reports.report', compact(
            'page',
            'listReport',
            'pdfPath',
            'user',
            'prevReportId',
            'nextReportId',
            'currentPos',
            'siblingReports'
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

            // Simpan snapshot leader untuk keperluan partial reset
            Storage::disk('public')->copy($targetPath, $path . '/' . $listReport->Name_Procedure . '.leader.pdf');

            // Update waktu & Qr_Codes
            $listReport->Time_Approved_Leader = $request->input('timestamp');
            $listReport->Leader_Name = session('Username_User');
            if ($request->filled('qr_codes')) {
                $listReport->Qr_Codes = \App\Helpers\QrHelper::mergeQrCodes(
                    $listReport->Qr_Codes,
                    'leader',
                    $request->input('qr_codes')
                );
            }
            $listReport->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }

    public function createMonthlyTemplate(Request $request)
    {
        // Validasi input jika dikirim dari modal
        $validated = $request->validate([
            'source_year' => 'nullable|integer|min:2020|max:2099',
            'source_month' => 'nullable|integer|min:1|max:12',
            'target_year' => 'nullable|integer|min:2020|max:2099',
            'target_month' => 'nullable|integer|min:1|max:12',
        ]);

        if ($request->filled('source_year') && $request->filled('source_month') && $request->filled('target_year') && $request->filled('target_month')) {
            $sourceStart = Carbon::createFromDate($request->source_year, $request->source_month, 1)->startOfMonth();
            $sourceEnd = $sourceStart->copy()->endOfMonth();

            $targetStart = Carbon::createFromDate($request->target_year, $request->target_month, 1)->startOfMonth();
        } else {
            // Default fallback jika dipanggil tanpa parameter (bulan lalu -> bulan ini)
            $targetStart = now()->startOfMonth();
            $sourceStart = $targetStart->copy()->subMonth()->startOfMonth();
            $sourceEnd = $sourceStart->copy()->endOfMonth();
        }

        // Hindari timeout script
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        // Ambil ID Member yang memiliki laporan di bulan sumber
        $memberIds = Report::whereBetween('Start_Report', [$sourceStart->format('Y-m-d 00:00:00'), $sourceEnd->format('Y-m-d 23:59:59')])
            ->distinct()
            ->pluck('Id_Member');

        if ($memberIds->isEmpty()) {
            return redirect()->back()->with('warning', "Tidak ada data jobdesc di bulan {$sourceStart->format('F Y')} untuk dijadikan template.");
        }

        $createdCount = 0;
        $skippedCount = 0;

        $publicStoragePath = storage_path('app/public');

        $sourceUseRifa = MemberHelper::useRifa($sourceStart);
        $targetUseRifa = MemberHelper::useRifa($targetStart);

        foreach ($memberIds as $idMember) {
            // Tentukan targetIdMember:
            // Jika pindah era (dari sebelum Agustus 2026 ke Agustus 2026 ke atas),
            // konversikan Id_Member lokal ke id employee RIFA berdasarkan NIK.
            $targetIdMember = $idMember;
            if (! $sourceUseRifa && $targetUseRifa) {
                $sourceMember = Member::find($idMember);
                if ($sourceMember) {
                    $employee = Employee::where('nik', $sourceMember->NIK_Member)->first();
                    if ($employee) {
                        $targetIdMember = $employee->id;
                    }
                }
            } elseif ($sourceUseRifa && ! $targetUseRifa) {
                // Jika dari era RIFA ke era lama, cari ID lokal berdasarkan NIK
                $employee = Employee::find($idMember);
                if ($employee) {
                    $localMember = Member::where('NIK_Member', $employee->nik)->first();
                    if ($localMember) {
                        $targetIdMember = $localMember->Id_Member;
                    }
                }
            }

            // Cek apakah report untuk target member di tanggal 1 bulan target sudah ada
            if (Report::where('Id_Member', $targetIdMember)
                ->whereDate('Start_Report', $targetStart->format('Y-m-d'))
                ->exists()
            ) {
                $skippedCount++;
                continue;
            }

            // Ambil data report bulan sumber
            $sourceReport = Report::where('Id_Member', $idMember)
                ->whereBetween('Start_Report', [$sourceStart->format('Y-m-d 00:00:00'), $sourceEnd->format('Y-m-d 23:59:59')])
                ->orderBy('Start_Report', 'desc')
                ->first();

            if (! $sourceReport) {
                continue;
            }

            // Buat folder baru untuk bulan target
            $newFolder = $targetStart->format('Y-m-d') . '_' . $targetIdMember;
            $newFullPath = $publicStoragePath . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . $newFolder;
            if (! is_dir($newFullPath)) {
                @mkdir($newFullPath, 0755, true);
            }

            // Buat entri Report baru di bulan target dengan targetIdMember yang sudah sesuai era
            $newReport = Report::create([
                'Id_Member' => $targetIdMember,
                'Start_Report' => $targetStart->format('Y-m-d 00:00:00'),
                'Name_Report' => $sourceReport->Name_Report ?? '',
            ]);

            // Ambil semua prosedur yang tersimpan di List_Report sumber
            $oldListReports = List_Report::where('Id_Report', $sourceReport->Id_Report)->get();

            if ($oldListReports->isNotEmpty()) {
                $insertData = [];
                $sourceReportDate = Carbon::parse($sourceReport->Start_Report)->format('Y-m-d');
                $sourceFallbackDir = $publicStoragePath . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . $sourceReportDate . '_' . $idMember;

                foreach ($oldListReports as $item) {
                    $pdfFileName = $item->Name_Procedure . '.pdf';
                    $masterSourcePdfPath = $publicStoragePath . DIRECTORY_SEPARATOR . 'procedures' . DIRECTORY_SEPARATOR . $item->Name_Tractor . DIRECTORY_SEPARATOR . $item->Name_Area . DIRECTORY_SEPARATOR . $pdfFileName;
                    $targetPdfPath = $newFullPath . DIRECTORY_SEPARATOR . $pdfFileName;

                    // Salin master PDF bersih via operasi filesystem native (jauh lebih cepat dibanding Storage API)
                    if (is_file($masterSourcePdfPath)) {
                        @copy($masterSourcePdfPath, $targetPdfPath);
                    } elseif (is_file($sourceFallbackDir . DIRECTORY_SEPARATOR . $pdfFileName)) {
                        // Fallback jika di procedures/ belum ada
                        @copy($sourceFallbackDir . DIRECTORY_SEPARATOR . $pdfFileName, $targetPdfPath);
                    }

                    // Siapkan data untuk insert ke List_Report dalam kondisi KOSONGAN
                    $insertData[] = [
                        'Id_Report' => $newReport->Id_Report,
                        'Name_Procedure' => $item->Name_Procedure,
                        'Name_Area' => $item->Name_Area,
                        'Name_Tractor' => $item->Name_Tractor,
                        'Item_Procedure' => $item->Item_Procedure,
                        'Time_List_Report' => null,
                        'Time_Approved_Leader' => null,
                        'Time_Approved_Auditor' => null,
                        'Reporter_Name' => $item->Reporter_Name,
                        'Leader_Name' => null,
                        'Auditor_Name' => null,
                    ];
                }

                // Masukkan data List_Report baru secara chunked agar hemat memory dan efisien
                foreach (array_chunk($insertData, 500) as $chunk) {
                    List_Report::insert($chunk);
                }
            }

            $createdCount++;
        }

        $sourceLabel = $sourceStart->format('F Y');
        $targetLabel = $targetStart->format('F Y');

        if ($createdCount > 0) {
            $msg = "Berhasil menyalin template jobdesc untuk {$createdCount} member dari bulan {$sourceLabel} ke bulan {$targetLabel}. Status approval & tanda tangan telah dikosongkan.";
            if ($skippedCount > 0) {
                $msg .= " ({$skippedCount} member dilewati karena sudah ada data di bulan target).";
            }
            return redirect()->back()->with('success', $msg);
        } else {
            return redirect()->back()->with('info', "Tidak ada data baru yang dibuat. Semua ({$skippedCount}) member sudah memiliki data di bulan {$targetLabel} atau tidak ada data di {$sourceLabel}.");
        }
    }

    // 🔥 Fungsi Update
    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'Start_Report' => 'required|date',
            'Id_Member' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($request) {
                    if (! MemberHelper::exists($value, $request->Start_Report)) {
                        $fail('The selected member is invalid.');
                    }
                },
            ],
        ]);

        // Temukan report berdasarkan ID
        $report = Report::findOrFail($id);

        // Ambil data lama sebelum diupdate
        $oldStartReport = $report->Start_Report;
        $oldIdMember = $report->Id_Member;

        // Update data report
        $report->Start_Report = $request->Start_Report;
        $report->Id_Member = $request->Id_Member;
        $report->save();

        // Jika Start_Report atau Id_Member berubah, pindahkan folder lama ke yang baru
        if ($oldStartReport !== $request->Start_Report || $oldIdMember !== $request->Id_Member) {
            $oldFolderName = Carbon::parse($oldStartReport)->format('Y-m-d') . '_' . $oldIdMember;
            $newFolderName = Carbon::parse($request->Start_Report)->format('Y-m-d') . '_' . $request->Id_Member;

            $oldPath = 'reports/' . $oldFolderName;
            $newPath = 'reports/' . $newFolderName;

            if (Storage::disk('public')->exists($oldPath)) {
                // Hapus target dulu jika sudah ada (misal di Windows rename gagal jika target exists)
                if (Storage::disk('public')->exists($newPath)) {
                    Storage::disk('public')->deleteDirectory($newPath);
                }
                Storage::disk('public')->move($oldPath, $newPath);
            }
        }

        return redirect()->back()->with('success', 'Report updated successfully.');
    }

    // 🔥 Fungsi Destroy
    public function destroy($id)
    {
        $report = Report::findOrFail($id);

        // Format tanggal ke Y-m-d agar sesuai dengan nama folder sebenarnya
        $folderName = Carbon::parse($report->Start_Report)->format('Y-m-d') . '_' . $report->Id_Member;
        $fullPath = 'reports/' . $folderName;

        if (Storage::disk('public')->exists($fullPath)) {
            Storage::disk('public')->deleteDirectory($fullPath);
        }

        List_Report::where('Id_Report', $report->Id_Report)->delete();
        $report->delete();

        return redirect()->back()->with('success', 'Report deleted successfully.');
    }

    public function destroy_list_report($Id_List_Report)
    {
        $listReport = List_Report::with('report')->findOrFail($Id_List_Report);

        $id_member = $listReport->report->Id_Member;
        $startReport = Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');
        $pdfPath = "reports/{$startReport}_{$id_member}/{$listReport->Name_Procedure}.pdf";

        if (Storage::disk('public')->exists($pdfPath)) {
            Storage::disk('public')->delete($pdfPath);
        }

        $listReport->delete();

        return redirect()->back()->with('success', 'Prosedur berhasil dihapus dari laporan.');
    }

    public function reset_list_report(string $Id_List_Report)
    {
        // Hanya akun leader bernama Saiful yang boleh melakukan reset
        $loginUser = User::where('Id_User', session('Id_User'))->first();
        $isSaiful = $loginUser && strtolower($loginUser->Name_User ?? '') === 'saiful';
        if (! $isSaiful) {
            return redirect()->back()->withErrors(['error' => 'Hanya leader Saiful yang diizinkan untuk melakukan reset approval.']);
        }

        $role = request()->input('role', 'all'); // 'leader', 'auditor', atau 'all'

        $listReport = List_Report::with(['report'])->findOrFail($Id_List_Report);

        $id_member = $listReport->report->Id_Member;
        $timeReport = Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');
        $procedureName = $listReport->Name_Procedure;
        $basePath = 'reports/' . $timeReport . '_' . $id_member;
        $mainPdf = $basePath . '/' . $procedureName . '.pdf';

        if ($role === 'auditor') {
            // Reset hanya approval auditor — kembalikan PDF ke snapshot leader jika ada
            $leaderSnapshot = $basePath . '/' . $procedureName . '.leader.pdf';
            if (Storage::disk('public')->exists($leaderSnapshot)) {
                Storage::disk('public')->copy($leaderSnapshot, $mainPdf);
            }
            // Hapus snapshot auditor
            Storage::disk('public')->delete($basePath . '/' . $procedureName . '.auditor.pdf');

            $listReport->Time_Approved_Auditor = null;
            $listReport->Auditor_Name = null;
            $listReport->Qr_Codes = \App\Helpers\QrHelper::removeRole($listReport->Qr_Codes, 'auditor');

        } elseif ($role === 'leader') {
            // Reset approval leader dan auditor — kembalikan PDF ke snapshot member jika ada
            $memberSnapshot = $basePath . '/' . $procedureName . '.member.pdf';
            if (Storage::disk('public')->exists($memberSnapshot)) {
                Storage::disk('public')->copy($memberSnapshot, $mainPdf);
            }
            // Hapus snapshot leader dan auditor
            Storage::disk('public')->delete($basePath . '/' . $procedureName . '.leader.pdf');
            Storage::disk('public')->delete($basePath . '/' . $procedureName . '.auditor.pdf');

            $listReport->Time_Approved_Leader = null;
            $listReport->Leader_Name = null;
            $listReport->Time_Approved_Auditor = null;
            $listReport->Auditor_Name = null;
            $listReport->Qr_Codes = \App\Helpers\QrHelper::removeRole($listReport->Qr_Codes, 'leader');

        } else {
            // Reset semua (fallback legacy) — salin dari master procedures
            $sourcePath = 'procedures/' . $listReport->Name_Tractor . '/' . $listReport->Name_Area . '/' . $procedureName . '.pdf';
            if (Storage::disk('public')->exists($sourcePath)) {
                Storage::disk('public')->copy($sourcePath, $mainPdf);
            }
            // Hapus semua snapshot
            foreach (['member', 'leader', 'auditor'] as $snap) {
                Storage::disk('public')->delete($basePath . '/' . $procedureName . '.' . $snap . '.pdf');
            }

            $listReport->Time_List_Report = null;
            $listReport->Time_Approved_Leader = null;
            $listReport->Time_Approved_Auditor = null;
            $listReport->Leader_Name = null;
            $listReport->Auditor_Name = null;
            $listReport->Qr_Codes = null;
        }

        $listReport->save();

        return redirect()->back()->with('success', 'Approval berhasil direset.');
    }

    /**
     * Partial reset for replacement report items.
     *
     * @param  string  $Id_List_Report_Replacement
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reset_replacement_report(string $Id_List_Report_Replacement)
    {
        // Hanya akun leader bernama Saiful yang boleh melakukan reset
        $loginUser = User::where('Id_User', session('Id_User'))->first();
        $isSaiful = $loginUser && strtolower($loginUser->Name_User ?? '') === 'saiful';
        if (! $isSaiful) {
            return redirect()->back()->withErrors(['error' => 'Hanya leader Saiful yang diizinkan untuk melakukan reset approval.']);
        }

        $role = request()->input('role', 'all');

        $listReport = \App\Models\ListReportReplacement::findOrFail($Id_List_Report_Replacement);
        $idRepHeader = $listReport->Id_Report_Replacement;
        $procedureName = $listReport->Name_Procedure;
        $basePath = 'report_replacements/' . $idRepHeader;
        $mainPdf = $basePath . '/' . $procedureName . '.pdf';

        if ($role === 'auditor') {
            $leaderSnapshot = $basePath . '/' . $procedureName . '.leader.pdf';
            if (Storage::disk('public')->exists($leaderSnapshot)) {
                Storage::disk('public')->copy($leaderSnapshot, $mainPdf);
            }
            Storage::disk('public')->delete($basePath . '/' . $procedureName . '.auditor.pdf');

            $listReport->Time_Approved_Auditor = null;
            $listReport->Auditor_Name = null;
            $listReport->Qr_Codes = \App\Helpers\QrHelper::removeRole($listReport->Qr_Codes, 'auditor');

        } elseif ($role === 'leader') {
            $memberSnapshot = $basePath . '/' . $procedureName . '.member.pdf';
            if (Storage::disk('public')->exists($memberSnapshot)) {
                Storage::disk('public')->copy($memberSnapshot, $mainPdf);
            }
            Storage::disk('public')->delete($basePath . '/' . $procedureName . '.leader.pdf');
            Storage::disk('public')->delete($basePath . '/' . $procedureName . '.auditor.pdf');

            $listReport->Time_Approved_Leader = null;
            $listReport->Leader_Name = null;
            $listReport->Time_Approved_Auditor = null;
            $listReport->Auditor_Name = null;
            $listReport->Qr_Codes = \App\Helpers\QrHelper::removeRole($listReport->Qr_Codes, 'leader');

        } else {
            $sourcePath = 'procedures/' . $listReport->Name_Tractor . '/' . $listReport->Name_Area . '/' . $procedureName . '.pdf';
            if (Storage::disk('public')->exists($sourcePath)) {
                Storage::disk('public')->copy($sourcePath, $mainPdf);
            }
            foreach (['member', 'leader', 'auditor'] as $snap) {
                Storage::disk('public')->delete($basePath . '/' . $procedureName . '.' . $snap . '.pdf');
            }

            $listReport->Time_List_Report = null;
            $listReport->Time_Approved_Leader = null;
            $listReport->Time_Approved_Auditor = null;
            $listReport->Leader_Name = null;
            $listReport->Auditor_Name = null;
            $listReport->Qr_Codes = null;
        }

        $listReport->save();

        return redirect()->back()->with('success', 'Approval pengganti berhasil direset.');
    }


    /**
     * Upload / salin ulang file PDF dari master data procedure untuk sebuah report
     * berdasarkan data master di DB (tabel procedures) selama item list_report belum ada approval sama sekali.
     */
    public function syncMasterPdf($Id_Report)
    {
        $loginUser = User::where('Id_User', session('Id_User'))->first();
        $isSaiful = $loginUser && (
            strtolower($loginUser->Username_User ?? '') === 'saiful' ||
            strtolower($loginUser->Name_User ?? '') === 'saiful'
        );

        if (! $isSaiful) {
            return redirect()->back()->withErrors(['error' => 'Hanya akun Saiful yang diizinkan untuk mengupload ulang master PDF.']);
        }

        // Hindari timeout
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        $report = Report::findOrFail($Id_Report);
        $publicStoragePath = config('filesystems.disks.public.root', public_path('storage'));
        $appPublicStoragePath = storage_path('app/public');

        $timeReport = Carbon::parse($report->Start_Report)->format('Y-m-d');
        $targetDir = $publicStoragePath . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . $timeReport . '_' . $report->Id_Member;

        if (! is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        // Ambil list report yang belum ada approval sama sekali (member, leader, auditor)
        $unapprovedItems = List_Report::where('Id_Report', $report->Id_Report)
            ->whereNull('Time_List_Report')
            ->whereNull('Time_Approved_Leader')
            ->whereNull('Time_Approved_Auditor')
            ->get();

        if ($unapprovedItems->isEmpty()) {
            return redirect()->back()->with('warning', 'Tidak ada item unapproved yang dapat disinkronkan.');
        }

        $syncedCount = 0;
        $dbMatchedCount = 0;

        foreach ($unapprovedItems as $item) {
            $baseProcName = $item->display_name;

            // Cari data master procedure di database
            $procedure = Procedure::where('Name_Tractor', $item->Name_Tractor)
                ->where('Name_Area', $item->Name_Area)
                ->where(function ($q) use ($item, $baseProcName) {
                    $q->where('Name_Procedure', $item->Name_Procedure)
                      ->orWhere('Name_Procedure', $baseProcName);
                })
                ->first();

            if ($procedure) {
                $dbMatchedCount++;

                // Sinkronkan data prosedur jika ada pembaruan di master procedure
                if (! empty($procedure->Item_Procedure) && $item->Item_Procedure !== $procedure->Item_Procedure) {
                    $item->Item_Procedure = $procedure->Item_Procedure;
                    $item->save();
                }

                $procFileName = $procedure->Name_Procedure . '.pdf';
                $destPdfPath = $targetDir . DIRECTORY_SEPARATOR . $item->Name_Procedure . '.pdf';

                // Cek kemungkinan lokasi file master PDF
                $candidatePaths = [
                    $publicStoragePath . DIRECTORY_SEPARATOR . 'procedures' . DIRECTORY_SEPARATOR . $item->Name_Tractor . DIRECTORY_SEPARATOR . $item->Name_Area . DIRECTORY_SEPARATOR . $procFileName,
                    $appPublicStoragePath . DIRECTORY_SEPARATOR . 'procedures' . DIRECTORY_SEPARATOR . $item->Name_Tractor . DIRECTORY_SEPARATOR . $item->Name_Area . DIRECTORY_SEPARATOR . $procFileName,
                ];

                $copied = false;
                foreach ($candidatePaths as $sourcePath) {
                    if (is_file($sourcePath)) {
                        if (@copy($sourcePath, $destPdfPath)) {
                            $copied = true;
                            $syncedCount++;
                            break;
                        }
                    }
                }
            }
        }

        $message = "Berhasil memproses {$dbMatchedCount} prosedur unapproved berdasarkan master data DB (dengan {$syncedCount} file PDF tersalin).";
        return redirect()->back()->with('success', $message);
    }

    /**
     * Upload / salin ulang file PDF dari master data procedure untuk seluruh report dalam satu bulan
     * berdasarkan data master di DB (tabel procedures) selama item list_report belum ada approval sama sekali.
     */
    public function syncMonthMasterPdf($year, $month)
    {
        $loginUser = User::where('Id_User', session('Id_User'))->first();
        $isSaiful = $loginUser && (
            strtolower($loginUser->Username_User ?? '') === 'saiful' ||
            strtolower($loginUser->Name_User ?? '') === 'saiful'
        );

        if (! $isSaiful) {
            return redirect()->back()->withErrors(['error' => 'Hanya akun Saiful yang diizinkan untuk mengupload ulang master PDF.']);
        }

        // Hindari timeout eksekusi
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        $reports = Report::whereYear('Start_Report', $year)
            ->whereMonth('Start_Report', $month)
            ->get();

        if ($reports->isEmpty()) {
            return redirect()->back()->with('warning', 'Tidak ada data report pada bulan ini.');
        }

        $publicStoragePath = config('filesystems.disks.public.root', public_path('storage'));
        $appPublicStoragePath = storage_path('app/public');

        $totalSynced = 0;
        $totalDbMatched = 0;
        $processedReports = 0;

        foreach ($reports as $report) {
            $timeReport = Carbon::parse($report->Start_Report)->format('Y-m-d');
            $targetDir = $publicStoragePath . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . $timeReport . '_' . $report->Id_Member;

            if (! is_dir($targetDir)) {
                @mkdir($targetDir, 0755, true);
            }

            // Ambil hanya item yang belum diapprove sama sekali
            $unapprovedItems = List_Report::where('Id_Report', $report->Id_Report)
                ->whereNull('Time_List_Report')
                ->whereNull('Time_Approved_Leader')
                ->whereNull('Time_Approved_Auditor')
                ->get();

            if ($unapprovedItems->isEmpty()) {
                continue;
            }

            $processedReports++;

            foreach ($unapprovedItems as $item) {
                $baseProcName = $item->display_name;

                // Cari data master procedure di database
                $procedure = Procedure::where('Name_Tractor', $item->Name_Tractor)
                    ->where('Name_Area', $item->Name_Area)
                    ->where(function ($q) use ($item, $baseProcName) {
                        $q->where('Name_Procedure', $item->Name_Procedure)
                          ->orWhere('Name_Procedure', $baseProcName);
                    })
                    ->first();

                if ($procedure) {
                    $totalDbMatched++;

                    // Sinkronkan Item_Procedure jika di master DB ada perubahan
                    if (! empty($procedure->Item_Procedure) && $item->Item_Procedure !== $procedure->Item_Procedure) {
                        $item->Item_Procedure = $procedure->Item_Procedure;
                        $item->save();
                    }

                    $procFileName = $procedure->Name_Procedure . '.pdf';
                    $destPdfPath = $targetDir . DIRECTORY_SEPARATOR . $item->Name_Procedure . '.pdf';

                    // Cek kemungkinan lokasi file master PDF
                    $candidatePaths = [
                        $publicStoragePath . DIRECTORY_SEPARATOR . 'procedures' . DIRECTORY_SEPARATOR . $item->Name_Tractor . DIRECTORY_SEPARATOR . $item->Name_Area . DIRECTORY_SEPARATOR . $procFileName,
                        $appPublicStoragePath . DIRECTORY_SEPARATOR . 'procedures' . DIRECTORY_SEPARATOR . $item->Name_Tractor . DIRECTORY_SEPARATOR . $item->Name_Area . DIRECTORY_SEPARATOR . $procFileName,
                    ];

                    foreach ($candidatePaths as $sourcePath) {
                        if (is_file($sourcePath)) {
                            if (@copy($sourcePath, $destPdfPath)) {
                                $totalSynced++;
                                break;
                            }
                        }
                    }
                }
            }
        }

        if ($processedReports === 0) {
            return redirect()->back()->with('warning', 'Semua item jobdesc di bulan ini sudah memiliki approval atau tidak memiliki data.');
        }

        $msg = "Berhasil memproses data master procedure pada {$processedReports} report member ({$totalDbMatched} prosedur unapproved terverifikasi di master data DB, {$totalSynced} file PDF disinkronkan).";

        return redirect()->back()->with('success', $msg);
    }
}

