<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\List_Report;
use App\Helpers\MemberHelper;
use App\Models\Procedure;
use App\Models\Report;
use App\Models\Tractor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request; // Pastikan model ini diimpor
use Illuminate\Support\Facades\Storage; // Pastikan model ini diimpor

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

        return view('leaders.reports.list_report', compact('page', 'report', 'tractorReports', 'Id_Report', 'absensis'));
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

        return view('leaders.reports.list_report_replacement', compact('page', 'reportReplacement', 'report', 'repMember', 'list_reports'));
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
            } elseif (session('Id_Type_User') == 1) {
                $listReport->Time_Approved_Auditor = $request->input('timestamp');
                $listReport->Auditor_Name = session('Username_User');
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

        return view('leaders.reports.list_report_detail', compact('page', 'report', 'list_reports', 'procedures', 'Id_Report', 'tractor'));
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

            // Update waktu
            $listReport->Time_Approved_Leader = $request->input('timestamp');
            $listReport->Leader_Name = session('Username_User');
            $listReport->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }

    public function createMonthlyTemplate()
    {
        $firstDayThisMonth = now()->startOfMonth();
        $firstDayLastMonth = $firstDayThisMonth->copy()->subMonth()->startOfMonth();
        $lastDayLastMonth = $firstDayThisMonth->copy()->subDay();

        // Ambil ID Member yang memiliki laporan di bulan lalu
        $memberIds = Report::whereBetween('Start_Report', [$firstDayLastMonth, $lastDayLastMonth])
            ->distinct()
            ->pluck('Id_Member');

        if ($memberIds->isEmpty()) {
            return redirect()->back()->with('warning', 'Tidak ada data di bulan lalu untuk dijadikan template.');
        }

        $createdCount = 0;

        foreach ($memberIds as $idMember) {
            // Pastikan report untuk tanggal 1 bulan ini belum ada
            if (Report::where('Id_Member', $idMember)
                ->whereDate('Start_Report', $firstDayThisMonth)
                ->exists()
            ) {
                continue;
            }

            // Ambil data report bulan lalu untuk referensi nama member, dll.
            $lastReport = Report::where('Id_Member', $idMember)
                ->whereBetween('Start_Report', [$firstDayLastMonth, $lastDayLastMonth])
                ->orderBy('Start_Report', 'desc')
                ->first();

            if (! $lastReport) {
                continue;
            }

            // Buat folder baru untuk bulan ini
            $newFolder = $firstDayThisMonth->format('Y-m-d') . '_' . $idMember;
            $newPath = 'reports/' . $newFolder;
            if (! Storage::disk('public')->exists($newPath)) {
                Storage::disk('public')->makeDirectory($newPath);
            }

            // Buat entri Report baru
            $newReport = Report::create([
                'Id_Member' => $idMember,
                'Start_Report' => $firstDayThisMonth->format('Y-m-d'),
            ]);

            // 🔥 GANTI: Ambil semua prosedur yang TERSIMPAN di List_Report bulan lalu
            // agar kita tahu prosedur apa saja yang pernah ditambahkan ke report tersebut.
            // Kita tidak mengambil dari tabel Procedure secara keseluruhan karena bisa jadi
            // prosedur yang tersedia di tabel Procedure tidak semuanya digunakan/ditambahkan
            // ke report bulan lalu.
            $oldListReports = List_Report::where('Id_Report', $lastReport->Id_Report)->get();

            if ($oldListReports->isNotEmpty()) {
                $insertData = [];
                foreach ($oldListReports as $item) {
                    // 🔥 GANTI: Ambil file dari folder master prosedur
                    // Format path: procedures/{Name_Tractor}/{Name_Area}/{Name_Procedure}.pdf
                    $masterSourcePdf = "procedures/{$item->Name_Tractor}/{$item->Name_Area}/{$item->Name_Procedure}.pdf";
                    $newTargetPdf = "{$newPath}/{$item->Name_Procedure}.pdf";

                    // Cek apakah file master ada
                    if (Storage::disk('public')->exists($masterSourcePdf)) {
                        // Salin dari master ke folder report baru
                        Storage::disk('public')->copy($masterSourcePdf, $newTargetPdf);
                        // \Log::info("Copying from master: {$masterSourcePdf} to {$newTargetPdf}");
                    } else {
                        // Jika file master tidak ditemukan, log atau lewati
                        // \Log::warning("Master file not found: {$masterSourcePdf}");
                    }

                    // Siapkan data untuk insert ke List_Report
                    $insertData[] = [
                        'Id_Report' => $newReport->Id_Report,
                        'Name_Procedure' => $item->Name_Procedure,
                        'Name_Area' => $item->Name_Area,
                        'Name_Tractor' => $item->Name_Tractor,
                        'Item_Procedure' => $item->Item_Procedure,
                        'Time_List_Report' => null,
                        'Time_Approved_Leader' => null,
                        'Time_Approved_Auditor' => null,
                        'Reporter_Name' => $item->Reporter_Name, // Bisa diupdate jika perlu
                        'Leader_Name' => null,
                        'Auditor_Name' => null,
                    ];
                }
                // Masukkan semua data List_Report baru sekaligus
                List_Report::insert($insertData);
            }

            $createdCount++;
        }

        if ($createdCount > 0) {
            return redirect()->back()->with('success', "Berhasil buat template untuk {$createdCount} member di tanggal 1 bulan ini dari file master.");
        } else {
            return redirect()->back()->with('info', 'Template bulan ini sudah ada atau tidak ada laporan bulan lalu untuk diproses.');
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
        $listReport = List_Report::with(['report'])->findOrFail($Id_List_Report);

        $nameTractor = $listReport->Name_Tractor;
        $nameArea = $listReport->Name_Area;
        $procedureName = $listReport->Name_Procedure;

        $sourcePath = 'procedures/' . $nameTractor . '/' . $nameArea . '/' . $procedureName . '.pdf';

        $timeReport = Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');

        $fullPath = 'reports/' . $timeReport . '_' . $listReport->report->Id_Member;

        $targetPath = $fullPath . '/' . $procedureName . '.pdf';

        // copy dan replace file dari procedures ke reports jika file target ada
        if (Storage::disk('public')->exists($sourcePath)) {
            Storage::disk('public')->copy($sourcePath, $targetPath);
        }

        // Reset approval timestamps and names
        $listReport->Time_Approved_Leader = null;
        $listReport->Time_Approved_Auditor = null;
        $listReport->Time_List_Report = null;
        $listReport->Leader_Name = null;
        $listReport->Auditor_Name = null;
        $listReport->save();

        return redirect()->back()->with('success', 'Approval berhasil direset.');
    }
}
