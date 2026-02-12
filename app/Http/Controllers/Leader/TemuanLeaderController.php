<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Http\Helper\JsonHelper;
use App\Models\Temuan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TemuanLeaderController extends Controller
{
    private string $base_path = 'storage/temuans/';

    public function index(Request $request)
    {
        $page = 'temuan';

        // Use session for month filter persistence
        if ($request->has('month')) {
            $month = $request->input('month');
            session(['last_temuan_month' => $month]);
        } else {
            $month = session('last_temuan_month') ?? Carbon::now()->format('Y-m');
        }

        [$year, $monthNum] = explode('-', $month);

        $query = Temuan::with(['ListReport.report.member', 'User'])
            ->whereNotNull('Time_Temuan')
            ->whereYear('Time_Temuan', $year)
            ->whereMonth('Time_Temuan', $monthNum);

        $temuans = $query->orderBy('Time_Temuan', 'desc')->get();

        // Group temuans by Tipe_Temuan
        $tipeTemuanCategories = [
            'Revisi prosedur' => [],
            'Perakitan tak sesuai' => [],
            'Shiyousho tak sesuai' => [],
            'Tidak perlu penanganan' => [],
            'Lain-lain' => [],
            'Uncategorized' => [],
        ];

        foreach ($temuans as $temuan) {
            $tipe = $temuan->Tipe_Temuan;

            if (empty($tipe)) {
                $tipeTemuanCategories['Uncategorized'][] = $temuan;
            } elseif (in_array($tipe, ['Revisi prosedur', 'Perakitan tak sesuai', 'Shiyousho tak sesuai', 'Tidak perlu penanganan'])) {
                $tipeTemuanCategories[$tipe][] = $temuan;
            } else {
                $tipeTemuanCategories['Lain-lain'][] = $temuan;
            }
        }

        return view('leaders.temuan.index', [
            'page' => $page,
            'temuans' => $temuans,
            'tipeTemuanCategories' => $tipeTemuanCategories,
            'month' => $month,
        ]);
    }

    public function updateTipeTemuan(Request $request, string $Id_Temuan)
    {
        $data = $request->validate([
            'tipe_temuan' => 'required|string|max:255',
            'tipe_temuan_custom' => 'nullable|string|max:255',
        ]);

        $temuan = Temuan::findOrFail($Id_Temuan);

        // If "Lain-lain" is selected, use custom input
        if ($data['tipe_temuan'] === 'Lain-lain' && ! empty($data['tipe_temuan_custom'])) {
            $temuan->Tipe_Temuan = $data['tipe_temuan_custom'];
        } else {
            $temuan->Tipe_Temuan = $data['tipe_temuan'];
        }

        $temuan->save();

        return redirect()->back()->with('success', 'Tipe Temuan berhasil diperbarui.');
    }

    public function deleteTemuan(string $Id_Temuan)
    {
        $temuan = Temuan::findOrFail($Id_Temuan);

        try {
            $this->deleteTemuanFile($temuan);
            $temuan->delete();

            return redirect()->route('leader-temuan.list')->with('success', 'Temuan deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting temuan: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->route('leader-temuan.list')->with('error', 'Failed to delete Temuan. Please check the logs.');
        }
    }

    private function deleteTemuanFile(Temuan $temuan): void
    {
        $objectdata = new JsonHelper($temuan->Object_Temuan);
        $filePath = $objectdata->get('File_Path', '');

        if (! $filePath) {
            return;
        }

        // Convert to absolute path
        $absolutePath = Str::startsWith($filePath, ['http://', 'https://'])
            ? public_path(parse_url($filePath, PHP_URL_PATH))
            : public_path($filePath);

        // Delete file if exists
        if (file_exists($absolutePath) && is_file($absolutePath)) {
            unlink($absolutePath);
            Log::info("Deleted temuan file: {$absolutePath}");

            // Remove parent directory if empty
            $parentDir = dirname($absolutePath);
            if ($this->isDirectoryEmpty($parentDir) && $this->isSafeToDelete($parentDir)) {
                rmdir($parentDir);
                Log::info("Removed empty directory: {$parentDir}");
            }
        } else {
            Log::warning("Temuan file not found: {$absolutePath}");
        }
    }

    private function isDirectoryEmpty(string $dir): bool
    {
        return is_dir($dir) && count(scandir($dir)) === 2;
    }

    private function isSafeToDelete(string $dir): bool
    {
        $safeRoots = [
            public_path('storage'),
            storage_path('app/public'),
        ];

        foreach ($safeRoots as $root) {
            if (Str::startsWith($dir, $root) && $dir !== $root) {
                return true;
            }
        }

        return false;
    }

    public function show(string $Id_Temuan)
    {
        $page = 'temuan';
        $temuan = Temuan::with(['ListReport.report.member', 'User'])->findOrFail($Id_Temuan);
        $listReport = $temuan->ListReport;

        $id_member = $listReport->report->member->Id_Member;
        $timeReport = Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');
        $fullPath = 'storage/reports/'.$timeReport.'_'.$id_member;
        $fileName = $listReport->Name_Procedure.'.pdf';
        $pdfPath = $fullPath.'/'.$fileName;
        $current_user = User::find(session('Id_User'));

        $jsonData = new JsonHelper($temuan->Object_Temuan);
        $object = $jsonData;

        return view('leaders.temuan.temuan_show', [
            'page' => $page,
            'temuan' => $temuan,
            'listReport' => $listReport,
            'pdfPath' => $pdfPath,
            'object' => $object,
            'current_user' => $current_user,
        ]);
    }

    public function submitPenanganan(Request $request)
    {
        $data = $request->validate([
            'Id_Temuan' => 'required|exists:temuans,Id_Temuan',
            'pdf' => 'required|file',
            'timestamp' => 'required',
            'comments' => 'nullable|string',
        ]);

        return DB::transaction(function () use ($request, $data) {
            $temuan = Temuan::with(['ListReport.report.member', 'User'])->findOrFail($data['Id_Temuan']);
            $currentUser = User::find(session('Id_User'));
            $timeReport = Carbon::parse($temuan->ListReport->report->Start_Report)->format('Y-m-d');
            $jsonData = new JsonHelper($temuan->Object_Temuan);
            $jsonData->Name_User_Penanganan = $currentUser->Name_User;
            $jsonData->Comments_Penanganan = json_decode($data['comments'], true) ?? [];

            // Handle file upload
            if ($request->hasFile('pdf')) {
                $pdf = $request->file('pdf');

                $relativePath = $this->base_path.$temuan->Id_User.'_'.$timeReport.'_'.$temuan->ListReport->report->member->id;
                $directory = public_path($relativePath);

                if (! file_exists($directory)) {
                    if (! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                        throw new \RuntimeException('Failed to create directory: '.$directory);
                    }
                }

                $filename = 'PN _ '.$temuan->Id_Temuan.' _ '.$temuan->ListReport->Name_Procedure.'.pdf';

                if (! $pdf->move($directory, $filename)) {
                    throw new \RuntimeException('Failed to move uploaded file');
                }
                $jsonData->File_Path_Penanganan = $relativePath.'/'.$filename;
                $jsonData->Is_Submit_Penanganan = true;

                $temuan->Object_Temuan = $jsonData;
                $temuan->Time_Penanganan = Carbon::now()->toDateTimeString();
                $temuan->save();
                debugbar()->log($jsonData);

                return ['success' => true,
                    'message' => 'Penanganan submitted successfully.',
                ];
            }

            return ['success' => false,
                'message' => 'Failed to submit Penanganan. Please try again.',
            ];
        });
    }

    public function createPenanganan(Request $request)
    {
        $data = $request->validate([
            'Id_Temuan' => 'required|exists:temuans,Id_Temuan',
            'photo_pdf' => 'required|file',
        ]);

        $temuan = Temuan::with(['ListReport.report.member', 'User'])->findOrFail($data['Id_Temuan']);
        $currentUser = User::find(session('Id_User'));
        $timeReport = Carbon::parse($temuan->ListReport->report->Start_Report)->format('Y-m-d');

        return DB::transaction(function () use ($request, $temuan, $timeReport) {
            $jsonData = new JsonHelper($temuan->Object_Temuan);
            $jsonData->UploudFoto_Time_Penanganan = Carbon::now()->toDateTimeString();

            // Handle file upload
            if ($request->hasFile('photo_pdf')) {
                $photo_pdf = $request->file('photo_pdf');

                $relativePath = $this->base_path.$temuan->Id_User.'_'.$timeReport.'_'.$temuan->ListReport->report->member->id;
                $directory = public_path($relativePath);

                if (! file_exists($directory)) {
                    if (! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                        throw new \RuntimeException('Failed to create directory: '.$directory);
                    }
                }

                $extension = $photo_pdf->getClientOriginalExtension();
                $fileName = 'PN _ '.$temuan->Id_Temuan.' _ '.$temuan->ListReport->Name_Procedure.'.'.$extension;

                if (! $photo_pdf->move($directory, $fileName)) {
                    throw new \RuntimeException('Failed to move uploaded file');
                }
                $jsonData->File_Path_Penanganan = $relativePath.'/'.$fileName;

                $temuan->Object_Temuan = $jsonData;
            }
            $temuan->save();

            return response()->json([
                'success' => true,
                'message' => 'Penanganan submitted successfully.',
            ]);
        });
    }

    public function getMonthlyStatistics(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        [$year, $monthNum] = explode('-', $month);

        $temuans = Temuan::with(['ListReport.report.member', 'User'])
            ->whereNotNull('Time_Temuan')
            ->whereYear('Time_Temuan', $year)
            ->whereMonth('Time_Temuan', $monthNum)
            ->get();

        $statistics = [
            'total' => $temuans->count(),
            'categories' => [
                'Revisi prosedur' => $this->getCategoryStats($temuans, 'Revisi prosedur'),
                'Perakitan tak sesuai' => $this->getCategoryStats($temuans, 'Perakitan tak sesuai'),
                'Shiyousho tak sesuai' => $this->getCategoryStats($temuans, 'Shiyousho tak sesuai'),
                'Tidak perlu penanganan' => $this->getCategoryStats($temuans, 'Tidak perlu penanganan'),
                'Lain-lain' => $this->getOtherCategoryStats($temuans),
            ],
            'month' => $month,
            'monthName' => Carbon::createFromFormat('Y-m', $month)->format('F Y'),
        ];

        return response()->json($statistics);
    }

    /**
     * Get statistics for specific category
     */
    private function getCategoryStats($temuans, $category)
    {
        $categoryTemuans = $temuans->filter(function ($temuan) use ($category) {
            return $temuan->Tipe_Temuan === $category;
        });

        return [
            'total' => $categoryTemuans->count(),
            'belum_penanganan' => $categoryTemuans->filter(function ($temuan) {
                return is_null($temuan->Time_Penanganan);
            })->count(),
            'menunggu_validasi' => $categoryTemuans->filter(function ($temuan) {
                return ! is_null($temuan->Time_Penanganan) && ! $temuan->Status_Temuan;
            })->count(),
            'sudah_tervalidasi' => $categoryTemuans->filter(function ($temuan) {
                return ! is_null($temuan->Time_Penanganan) && $temuan->Status_Temuan;
            })->count(),
        ];
    }

    /**
     * Get statistics for "Lain-lain" category (custom types)
     */
    private function getOtherCategoryStats($temuans)
    {
        $otherTemuans = $temuans->filter(function ($temuan) {
            return ! empty($temuan->Tipe_Temuan) &&
                ! in_array($temuan->Tipe_Temuan, ['Revisi prosedur', 'Perakitan tak sesuai', 'Shiyousho tak sesuai', 'Tidak perlu penanganan']);
        });

        return [
            'total' => $otherTemuans->count(),
            'belum_penanganan' => $otherTemuans->filter(function ($temuan) {
                return is_null($temuan->Time_Penanganan);
            })->count(),
            'menunggu_validasi' => $otherTemuans->filter(function ($temuan) {
                return ! is_null($temuan->Time_Penanganan) && ! $temuan->Status_Temuan;
            })->count(),
            'sudah_tervalidasi' => $otherTemuans->filter(function ($temuan) {
                return ! is_null($temuan->Time_Penanganan) && $temuan->Status_Temuan;
            })->count(),
        ];
    }

    public function getMissingStatistics()
    {
        $now = Carbon::now();

        // Temuan yang sudah 3 hari belum dikategorikan
        $uncategorized = Temuan::whereNotNull('Time_Temuan')
            ->where(function ($query) {
                $query->whereNull('Tipe_Temuan')
                    ->orWhere('Tipe_Temuan', '');
            })
            ->where('Time_Temuan', '<=', Carbon::now()->subDays(3))
            ->count();

        // Temuan yang sudah 15 hari belum ada penanganan (kecuali "Tidak perlu penanganan")
        $noPenanganan = Temuan::whereNotNull('Time_Temuan')
            ->whereNull('Time_Penanganan')
            ->where(function ($query) {
                $query->where('Tipe_Temuan', '!=', 'Tidak perlu penanganan')
                    ->orWhereNull('Tipe_Temuan');
            })
            ->where('Time_Temuan', '<=', Carbon::now()->subDays(15))
            ->count();

        $statistics = [
            'uncategorized_3days' => $uncategorized,
            'no_penanganan_15days' => $noPenanganan,
            'total_missing' => $uncategorized + $noPenanganan,
        ];

        return response()->json($statistics);
    }

    public function missingTemuan()
    {
        $page = 'temuan';

        // Temuan yang sudah 3 hari belum dikategorikan
        $uncategorizedTemuans = Temuan::with(['ListReport.report.member', 'User'])
            ->whereNotNull('Time_Temuan')
            ->where(function ($query) {
                $query->whereNull('Tipe_Temuan')
                    ->orWhere('Tipe_Temuan', '');
            })
            ->where('Time_Temuan', '<=', Carbon::now()->subDays(3))
            ->orderBy('Time_Temuan', 'asc')
            ->get();

        // Temuan yang sudah 15 hari belum ada penanganan (kecuali "Tidak perlu penanganan")
        $noPenangananTemuans = Temuan::with(['ListReport.report.member', 'User'])
            ->whereNotNull('Time_Temuan')
            ->whereNull('Time_Penanganan')
            ->where(function ($query) {
                $query->where('Tipe_Temuan', '!=', 'Tidak perlu penanganan')
                    ->orWhereNull('Tipe_Temuan');
            })
            ->where('Time_Temuan', '<=', Carbon::now()->subDays(15))
            ->orderBy('Time_Temuan', 'asc')
            ->get();

        return view('leaders.temuan.missing', [
            'page' => $page,
            'uncategorizedTemuans' => $uncategorizedTemuans,
            'noPenangananTemuans' => $noPenangananTemuans,
        ]);
    }
}
