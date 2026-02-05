<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\List_Report;
use App\Models\Member; // Pastikan diimpor
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
            ->with('member') // Pastikan relasi 'member' didefinisikan di model Report
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
            $folderName = $startReportDate.'_'.$id_member;
            $fullPath = 'reports/'.$folderName;
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

        return view('leaders.reports.list_report', compact('page', 'report', 'tractorReports', 'Id_Report'));
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

        $name_member = $report->member->nama ?? 'Unknown';
        $id_member = $report->member->id;
        $timeReport = Carbon::parse($report->Start_Report)->format('Y-m-d');

        $fullPath = 'reports/'.$timeReport.'_'.$id_member;

        if ($procedures->count() > 0) {
            $data = [];

            foreach ($procedures as $procedure) {
                $nameArea = $procedure->Name_Area;
                $nameTractor = $procedure->Name_Tractor;

                // Tambahkan id_member ke Pic_Procedure jika belum ada
                $picProcedure = $procedure->Pic_Procedure ?? [];
                if (!in_array($id_member, $picProcedure)) {
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

                $sourcePath = 'procedures/'.$nameTractor.'/'.$nameArea.'/'.$procedure->Name_Procedure.'.pdf';
                $targetName = $procedure->Name_Procedure.'.pdf';
                $targetPath = $fullPath.'/'.$targetName;

                if (Storage::disk('public')->exists($sourcePath)) {
                    Storage::disk('public')->copy($sourcePath, $targetPath);
                }
            }

            List_Report::insert($data);
        }

        return redirect()->back()->with('success', 'Report berhasil disimpan dan PIC ditambahkan.');
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

        return view('leaders.reports.report', compact('page', 'listReport', 'pdfPath', 'user'));
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
            $newFolder = $firstDayThisMonth->format('Y-m-d').'_'.$idMember;
            $newPath = 'reports/'.$newFolder;
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
            'Id_Member' => ['required', 'integer', 'exists:App\Models\Member,id'], // Validasi Id_Member ada di tabel members
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

        // Jika Start_Report atau Id_Member berubah, kita mungkin perlu memindahkan folder lama ke yang baru
        // Jika tidak, bisa diabaikan
        if ($oldStartReport !== $request->Start_Report || $oldIdMember !== $request->Id_Member) {
            // $oldFolderName = $oldStartReport . '_' . $oldIdMember;
            // $newFolderName = $request->Start_Report . '_' . $request->Id_Member;
            $oldFolderName = Carbon::parse($oldStartReport)->format('Y-m-d').'_'.$oldIdMember;
            $newFolderName = Carbon::parse($request->Start_Report)->format('Y-m-d').'_'.$request->Id_Member;

            $oldPath = 'reports/'.$oldFolderName;
            $newPath = 'reports/'.$newFolderName;

            // Jika folder lama ada, coba pindahkan
            if (Storage::disk('public')->exists($oldPath)) {
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
        $folderName = Carbon::parse($report->Start_Report)->format('Y-m-d').'_'.$report->Id_Member;
        $fullPath = 'reports/'.$folderName;

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

        $sourcePath = 'procedures/'.$nameTractor.'/'.$nameArea.'/'.$procedureName.'.pdf';

        $timeReport = Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');

        $fullPath = 'reports/'.$timeReport.'_'.$listReport->report->Id_Member;

        $targetPath = $fullPath.'/'.$procedureName.'.pdf';

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
