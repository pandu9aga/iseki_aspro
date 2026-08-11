<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\List_Training;
use App\Helpers\MemberHelper;
use App\Models\Procedure;
use App\Models\Training;
use App\Models\Tractor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request; // Pastikan model ini diimpor
use Illuminate\Support\Facades\Storage; // Pastikan model ini diimpor

class TrainingController extends Controller
{
    public function index()
    {
        $page = 'training';

        return view('leaders.trainings.index', compact('page'));
    }

    public function reporter($year, $month)
    {
        $page = 'training';

        $reports = Training::whereYear('Start_Training', $year)
            ->whereMonth('Start_Training', $month)
            ->orderBy('Start_Training')
            ->get();

        // Tentukan sumber data member berdasarkan bulan training, bukan tanggal hari ini
        $reportDate = Carbon::createFromDate($year, $month, 1)->format('Y-m-d');
        $members = MemberHelper::getAllMembers($reportDate);

        return view('leaders.trainings.reporter', compact('page', 'reports', 'members', 'year', 'month'));
    }

    public function create_reporter(Request $request)
    {
        $request->validate([
            'Id_Member' => 'required|array',
            'Start_Training' => 'required|date',
        ]);

        // Ambil hanya tanggalnya saja (tanpa jam)
        $startTrainingDate = date('Y-m-d', strtotime($request->Start_Training));

        foreach ($request->Id_Member as $id_member) {
            // Validasi member ada di sumber data yang sesuai dengan tanggal training
            if (! MemberHelper::exists($id_member, $startTrainingDate)) {
                continue;
            }

            // Cek apakah kombinasi Id_Member dan tanggal yang sama sudah ada
            $exists = Training::where('Id_Member', $id_member)
                ->whereDate('Start_Training', $startTrainingDate)
                ->exists();

            if ($exists) {
                continue; // Lewati jika sudah ada
            }

            // Buat folder dasar untuk member
            $folderName = $startTrainingDate . '_' . $id_member;
            $fullPath = 'trainings/' . $folderName;
            if (! Storage::disk('public')->exists($fullPath)) {
                Storage::disk('public')->makeDirectory($fullPath);
            }

            // Simpan ke tabel trainings
            Training::create([
                'Id_Member' => $id_member,
                'Start_Training' => $startTrainingDate, // hanya tanggal
            ]);
        }

        return redirect()->back()->with('success', 'Training berhasil disimpan.');
    }

    public function list_report(string $Id_Training)
    {
        $page = 'training';

        $report = Training::where('Id_Training', $Id_Training)->first();
        $tractors = Tractor::select('Name_Tractor', 'Photo_Tractor')
            ->distinct()
            ->orderBy('Name_Tractor')
            ->get();

        // Hitung jumlah prosedur per tractor dalam 1 query (hindari N+1)
        $counts = List_Training::where('Id_Training', $Id_Training)
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

        return view('leaders.trainings.list_report', compact('page', 'report', 'tractorReports', 'Id_Training'));
    }

    public function list_report_detail(string $Id_Training, string $Name_Tractor)
    {
        $page = 'training';

        $report = Training::where('Id_Training', $Id_Training)->first();
        $list_reports = List_Training::where('Id_Training', $Id_Training)->where('Name_Tractor', $Name_Tractor)->with('training')->orderBy('Name_Procedure')->get();

        $tractor = Tractor::where('Name_Tractor', $Name_Tractor)->first();

        $usedProcedures = $list_reports->pluck('Name_Procedure')->toArray();
        $procedures = Procedure::whereNotIn('Name_Procedure', $usedProcedures)
            ->where('Name_Tractor', $Name_Tractor)
            ->orderBy('Name_Procedure')
            ->get(['Name_Procedure']);

        return view('leaders.trainings.list_report_detail', compact('page', 'report', 'list_reports', 'procedures', 'Id_Training', 'tractor'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'Id_Training' => 'required|string',
            'Name_Procedure' => 'required|array',
        ]);

        $report = Training::where('Id_Training', $request->Id_Training)->first();

        if (! $report || ! $report->member) {
            return redirect()->back()->withErrors(['error' => 'Training atau data member tidak ditemukan.']);
        }

        $procedures = Procedure::whereIn('Name_Procedure', $request->Name_Procedure)->get();

        $member = $report->member;
        $name_member = $member->Name_Member ?? 'Unknown';
        $id_member = $member->Id_Member;
        $timeReport = Carbon::parse($report->Start_Training)->format('Y-m-d');

        $fullPath = 'trainings/' . $timeReport . '_' . $id_member;

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
                    'Id_Training' => $report->Id_Training,
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

            List_Training::insert($data);
        }

        return redirect()->back()->with('success', 'Training berhasil disimpan dan PIC ditambahkan.');
    }

    public function report(Request $request, $Id_List_Training)
    {
        $page = 'training';

        $Id_User = session('Id_User');
        $user = User::where('Id_User', $Id_User)->first();

        $listReport = List_Training::with('training')->findOrFail($Id_List_Training);

        $id_member = $listReport->training->member->Id_Member;
        $timeReport = Carbon::parse($listReport->training->Start_Training)->format('Y-m-d');

        $fullPath = 'storage/trainings/' . $timeReport . '_' . $id_member;

        $fileName = $listReport->Name_Procedure . '.pdf';
        $pdfPath = $fullPath . '/' . $fileName;

        $context = $request->query('context');

        if ($context === 'audit') {
            $date = $request->query('date');
            $auditorName = $request->query('auditorName');

            $query = List_Training::whereDate('Time_Approved_Auditor', $date);

            if ($auditorName === 'Unknown Auditor') {
                $query->whereNull('Auditor_Name');
            } else {
                $query->where('Auditor_Name', $auditorName);
            }

            $siblingReports = $query->orderBy('Id_List_Training')->pluck('Id_List_Training')->toArray();
        } else {
            // Get sibling list trainings for prev/next navigation
            $siblingReports = List_Training::where('Id_Training', $listReport->Id_Training)
                ->where('Name_Tractor', $listReport->Name_Tractor)
                ->orderBy('Name_Procedure')
                ->pluck('Id_List_Training')
                ->toArray();
        }

        $currentIndex = array_search($Id_List_Training, $siblingReports);
        $prevReportId = ($currentIndex !== false && $currentIndex > 0) ? $siblingReports[$currentIndex - 1] : null;
        $nextReportId = ($currentIndex !== false && $currentIndex < count($siblingReports) - 1) ? $siblingReports[$currentIndex + 1] : null;
        $currentPos = $currentIndex !== false ? $currentIndex + 1 : 0;

        return view('leaders.trainings.report', compact(
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

    public function submit_report(Request $request, $Id_List_Training)
    {
        $listReport = List_Training::with('training')->findOrFail($Id_List_Training);

        $id_member = $listReport->training->member->Id_Member;
        $timeReport = Carbon::parse($listReport->training->Start_Training)->format('Y-m-d');

        if ($request->hasFile('pdf')) {
            $request->validate([
                'pdf' => 'required|file|mimes:pdf|max:20480',
            ]);

            $path = 'trainings/' . $timeReport . '_' . $id_member;
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

        // Ambil ID Member yang memiliki training di bulan lalu
        $memberIds = Training::whereBetween('Start_Training', [$firstDayLastMonth, $lastDayLastMonth])
            ->distinct()
            ->pluck('Id_Member');

        if ($memberIds->isEmpty()) {
            return redirect()->back()->with('warning', 'Tidak ada data di bulan lalu untuk dijadikan template.');
        }

        $createdCount = 0;

        foreach ($memberIds as $idMember) {
            // Pastikan training untuk tanggal 1 bulan ini belum ada
            if (Training::where('Id_Member', $idMember)
                ->whereDate('Start_Training', $firstDayThisMonth)
                ->exists()
            ) {
                continue;
            }

            // Ambil data training bulan lalu untuk referensi nama member, dll.
            $lastReport = Training::where('Id_Member', $idMember)
                ->whereBetween('Start_Training', [$firstDayLastMonth, $lastDayLastMonth])
                ->orderBy('Start_Training', 'desc')
                ->first();

            if (! $lastReport) {
                continue;
            }

            // Buat folder baru untuk bulan ini
            $newFolder = $firstDayThisMonth->format('Y-m-d') . '_' . $idMember;
            $newPath = 'trainings/' . $newFolder;
            if (! Storage::disk('public')->exists($newPath)) {
                Storage::disk('public')->makeDirectory($newPath);
            }

            // Buat entri Training baru
            $newReport = Training::create([
                'Id_Member' => $idMember,
                'Start_Training' => $firstDayThisMonth->format('Y-m-d'),
            ]);

            // Ambil semua prosedur yang TERSIMPAN di List_Training bulan lalu
            $oldListReports = List_Training::where('Id_Training', $lastReport->Id_Training)->get();

            if ($oldListReports->isNotEmpty()) {
                $insertData = [];
                foreach ($oldListReports as $item) {
                    // Ambil file dari folder master prosedur
                    $masterSourcePdf = "procedures/{$item->Name_Tractor}/{$item->Name_Area}/{$item->Name_Procedure}.pdf";
                    $newTargetPdf = "{$newPath}/{$item->Name_Procedure}.pdf";

                    // Cek apakah file master ada
                    if (Storage::disk('public')->exists($masterSourcePdf)) {
                        // Salin dari master ke folder training baru
                        Storage::disk('public')->copy($masterSourcePdf, $newTargetPdf);
                    }

                    // Siapkan data untuk insert ke List_Training
                    $insertData[] = [
                        'Id_Training' => $newReport->Id_Training,
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
                // Masukkan semua data List_Training baru sekaligus
                List_Training::insert($insertData);
            }

            $createdCount++;
        }

        if ($createdCount > 0) {
            return redirect()->back()->with('success', "Berhasil buat template untuk {$createdCount} member di tanggal 1 bulan ini dari file master.");
        } else {
            return redirect()->back()->with('info', 'Template bulan ini sudah ada atau tidak ada training bulan lalu untuk diproses.');
        }
    }

    // 🔥 Fungsi Update
    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'Start_Training' => 'required|date',
            'Id_Member' => [
                'required',
                'integer',
                function ($attribute, $value, $fail) use ($request) {
                    if (! MemberHelper::exists($value, $request->Start_Training)) {
                        $fail('The selected member is invalid.');
                    }
                },
            ],
        ]);

        // Temukan training berdasarkan ID
        $report = Training::findOrFail($id);

        // Ambil data lama sebelum diupdate
        $oldStartTraining = $report->Start_Training;
        $oldIdMember = $report->Id_Member;

        // Update data training
        $report->Start_Training = $request->Start_Training;
        $report->Id_Member = $request->Id_Member;
        $report->save();

        // Jika Start_Training atau Id_Member berubah, pindahkan folder lama ke yang baru
        if ($oldStartTraining !== $request->Start_Training || $oldIdMember !== $request->Id_Member) {
            $oldFolderName = Carbon::parse($oldStartTraining)->format('Y-m-d') . '_' . $oldIdMember;
            $newFolderName = Carbon::parse($request->Start_Training)->format('Y-m-d') . '_' . $request->Id_Member;

            $oldPath = 'trainings/' . $oldFolderName;
            $newPath = 'trainings/' . $newFolderName;

            if (Storage::disk('public')->exists($oldPath)) {
                // Hapus target dulu jika sudah ada (misal di Windows rename gagal jika target exists)
                if (Storage::disk('public')->exists($newPath)) {
                    Storage::disk('public')->deleteDirectory($newPath);
                }
                Storage::disk('public')->move($oldPath, $newPath);
            }
        }

        return redirect()->back()->with('success', 'Training updated successfully.');
    }

    // 🔥 Fungsi Destroy
    public function destroy($id)
    {
        $report = Training::findOrFail($id);

        // Format tanggal ke Y-m-d agar sesuai dengan nama folder sebenarnya
        $folderName = Carbon::parse($report->Start_Training)->format('Y-m-d') . '_' . $report->Id_Member;
        $fullPath = 'trainings/' . $folderName;

        if (Storage::disk('public')->exists($fullPath)) {
            Storage::disk('public')->deleteDirectory($fullPath);
        }

        List_Training::where('Id_Training', $report->Id_Training)->delete();
        $report->delete();

        return redirect()->back()->with('success', 'Training deleted successfully.');
    }

    public function destroy_list_report($Id_List_Training)
    {
        $listReport = List_Training::with('training')->findOrFail($Id_List_Training);

        $id_member = $listReport->training->Id_Member;
        $startTraining = Carbon::parse($listReport->training->Start_Training)->format('Y-m-d');
        $pdfPath = "trainings/{$startTraining}_{$id_member}/{$listReport->Name_Procedure}.pdf";

        if (Storage::disk('public')->exists($pdfPath)) {
            Storage::disk('public')->delete($pdfPath);
        }

        $listReport->delete();

        return redirect()->back()->with('success', 'Prosedur berhasil dihapus dari training.');
    }

    public function reset_list_report(string $Id_List_Training)
    {
        $listReport = List_Training::with(['training'])->findOrFail($Id_List_Training);

        $nameTractor = $listReport->Name_Tractor;
        $nameArea = $listReport->Name_Area;
        $procedureName = $listReport->Name_Procedure;

        $sourcePath = 'procedures/' . $nameTractor . '/' . $nameArea . '/' . $procedureName . '.pdf';

        $timeReport = Carbon::parse($listReport->training->Start_Training)->format('Y-m-d');

        $fullPath = 'trainings/' . $timeReport . '_' . $listReport->training->Id_Member;

        $targetPath = $fullPath . '/' . $procedureName . '.pdf';

        // copy dan replace file dari procedures ke trainings jika file target ada
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
