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

    /**
     * Show list of all temuans with optional date filter
     *
     * @return \Illuminate\View\View
     *
     * @author tunbudi06
     */
    public function index(Request $request)
    {
        $page = 'temuan';
        $date = $request->input('date') ?? Carbon::today()->format('Y-m-d');

        $query = Temuan::with(['ListReport.report.member', 'User'])
            ->whereNotNull('Time_Temuan')
            ->whereDate('Time_Temuan', $date);

        $temuans = $query->orderBy('Time_Temuan', 'desc')->get();

        return view('leaders.temuan.index', [
            'page' => $page,
            'temuans' => $temuans,
            'date' => $date,
        ]);
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

    /**
     * Delete temuan file and empty parent directory
     */
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

    /**
     * Check if directory is empty
     */
    private function isDirectoryEmpty(string $dir): bool
    {
        return is_dir($dir) && count(scandir($dir)) === 2;
    }

    /**
     * Check if directory is safe to delete (within public/storage)
     */
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

    /**
     * Show temuan detail for penanganan by leader
     *
     * @return \Illuminate\View\View
     *
     * @author tunbudi06
     */
    public function show(string $Id_Temuan)
    {
        $page = 'temuan';
        $temuan = Temuan::with(['ListReport.report.member', 'User'])->findOrFail($Id_Temuan);
        $listReport = $temuan->ListReport;

        $id_member = $listReport->report->member->id;
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

    /**
     * Submit penanganan by leader
     *
     * @return array
     *
     * @author tunbudi06
     */
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
}
