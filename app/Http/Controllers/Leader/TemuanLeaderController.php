<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Http\Helper\JsonHelper;
use App\Models\List_Report;
use App\Models\Temuan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TemuanLeaderController extends Controller
{
    public function index()
    {
        $page = 'temuan';

        return view('leaders.temuan.index', compact('page'));
    }

    public function listDateTemuan(string $year, string $month)
    {
        $page = 'temuan';

        // Get all list reports with temuans for the specified month/year
        $listTemuan = List_Report::with([
            'Temuans',
            'report.member',
        ])
            ->whereHas('report', function ($query) use ($year, $month) {
                $query->whereYear('Start_Report', $year)
                    ->whereMonth('Start_Report', $month);
            })
            ->get();

        // Get unique users who have temuans
        $userIds = $listTemuan->pluck('Temuans')->flatten()->pluck('Id_User')->unique();
        $users = User::whereIn('Id_User', $userIds)->get();

        return view('leaders.temuan.list-user', [
            'page' => $page,
            'listTemuan' => $listTemuan,
            'users' => $users,
            'year' => $year,
            'month' => $month,
        ]);
    }

    public function listAllTemuan(string $Id_User)
    {
        $page = 'temuan';
        $user = User::where('Id_User', $Id_User)->first();

        // Get all list reports with temuans from this specific user
        $listTemuan = List_Report::with([
            'Temuans' => function ($query) use ($Id_User) {
                $query->where('Id_User', $Id_User);
            },
            'report.member',
        ])
            ->whereHas('Temuans', function ($query) use ($Id_User) {
                $query->where('Id_User', $Id_User);
            })
            ->get();

        return view('leaders.temuan.list_temuan', [
            'page' => $page,
            'user' => $user,
            'listTemuan' => $listTemuan,
        ]);
    }

    public function showTemuan(string $Id_List_Report, string $Id_User)
    {
        $page = 'temuan';
        $listReport = List_Report::where('Id_List_Report', $Id_List_Report)->first();

        $current_user = User::where('Id_User', $Id_User)->first();
        $id_member = $listReport->report->member->id;
        $timeReport = Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');
        $fullPath = 'storage/reports/'.$timeReport.'_'.$id_member;
        $fileName = $listReport->Name_Procedure.'.pdf';
        $pdfPath = $fullPath.'/'.$fileName;

        $baseModel = Temuan::where('Id_List_Report', $Id_List_Report)->where('Id_User', $Id_User);
        $listTemuan = $baseModel->whereNotNull('Time_Temuan')->get();
        $totalListTemuan = $listTemuan->count();

        $ListTemuanNull = $baseModel->whereNull('Time_Temuan')->first();
        $totalListTemuanNull = $ListTemuanNull ? 1 : 0;

        return view('leaders.temuan.temuan', [
            'page' => $page,
            'listReport' => $listReport,
            'current_user' => $current_user,
            'pdfPath' => $pdfPath,
            'listTemuan' => $listTemuan,
            'totalListTemuan' => $totalListTemuan,
            'ListTemuanNull' => array_merge([
                'Id_Temuan' => $ListTemuanNull?->Id_Temuan], $ListTemuanNull ? (new JsonHelper($ListTemuanNull->Object_Temuan))->toArray() : []) ?? [],
            'totalListTemuanNull' => $totalListTemuanNull,
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
}
