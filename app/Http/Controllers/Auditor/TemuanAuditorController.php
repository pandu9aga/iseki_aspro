<?php

namespace App\Http\Controllers\Auditor;

use App\Http\Controllers\Controller;
use App\Http\Helper\JsonHelper;
use App\Models\List_Report;
use App\Models\Temuan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class TemuanAuditorController extends Controller
{
    private string $base_path = 'storage/temuans/';

    public function temuan_report(string $Id_List_Report)
    {
        $page = 'temuan';
        $listReport = List_Report::where('Id_List_Report', $Id_List_Report)->first();

        $current_user = User::where('Id_User', session('Id_User'))->first();
        $id_member = $listReport->report->member->id;
        $timeReport = Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');
        $fullPath = 'storage/reports/'.$timeReport.'_'.$id_member;
        $fileName = $listReport->Name_Procedure.'.pdf';
        $pdfPath = $fullPath.'/'.$fileName;

        $listTemuan = Temuan::where('Id_List_Report', $Id_List_Report)->where('Id_User', $current_user->Id_User)->whereNotNull('Time_Temuan')->get();
        $totalListTemuan = $listTemuan->count();

        $ListTemuanNull = Temuan::where('Id_List_Report', $Id_List_Report)->where('Id_User', $current_user->Id_User)->whereNull('Time_Temuan')->first();
        $totalListTemuanNull = $ListTemuanNull ? 1 : 0;

        return view('auditors.temuan.temuan', [
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

    /**
     * Create temuan with file upload
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws Throwable
     *
     * @author tunbudi06
     */
    public function create_temuan(Request $request)
    {
        $data = $request->validate([
            'Id_List_Report' => 'required|int',
            'photo_pdf' => 'required|file',
        ]);

        $listReport = List_Report::with('report.member')->findOrFail($data['Id_List_Report']);
        $timeReport = Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');
        $current_user = User::where('Id_User', session('Id_User'))->firstOrFail();

        try {
            return DB::transaction(function () use ($request, $data, $listReport, $timeReport, $current_user) {
                $jsonData = new JsonHelper;
                $jsonData->UploudFoto_Time_Temuan = now()->toDateTimeString();
                $jsonData->Name_User_Temuan = $current_user->Name_User;
                $jsonData->File_Path_Temuan = '';
                $jsonData->Is_Submit_Penanganan = false;
                $jsonData->UploudFoto_Time_Penanganan = '';
                $jsonData->File_Path_Penanganan = '';
                $jsonData->Name_User_Penanganan = '';
                $jsonData->Validation_Notes = '';
                $jsonData->Validation_Time = '';

                $temuan = new Temuan;
                $temuan->Id_List_Report = $data['Id_List_Report'];
                $temuan->Id_User = $current_user->Id_User;
                $temuan->Object_Temuan = $jsonData->toJson();

                $temuan->save();

                if ($request->hasFile('photo_pdf')) {
                    $photo_pdf = $request->file('photo_pdf');

                    $relativePath = $this->base_path.$current_user->Id_User.'_'.$timeReport.'_'.$listReport->report->member->id;
                    $directory = public_path($relativePath);

                    if (! file_exists($directory)) {
                        if (! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                            throw new \RuntimeException('Failed to create directory: '.$directory);
                        }
                    }

                    $extension = $photo_pdf->getClientOriginalExtension();
                    $fileName = 'TM_'.$temuan->Id_Temuan.' _ '.$listReport->Name_Procedure.'.'.$extension;

                    if (! $photo_pdf->move($directory, $fileName)) {
                        throw new \RuntimeException('Failed to move uploaded file');
                    }

                    $jsonData->Photo_PDF_Temuan = $fileName;
                    $jsonData->File_Path_Temuan = $relativePath.'/'.$fileName;

                    $temuan->Object_Temuan = $jsonData;

                    $temuan->save();
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Temuan berhasil ditambahkan',
                    'data' => [
                        'Id_Temuan' => $temuan->Id_Temuan,
                        'file_path' => $jsonData->File_Path_Temuan ?? null,
                    ],
                ]);
            });
        } catch (\Exception $e) {
            Log::error('Failed to create temuan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menambahkan temuan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit temuan with annotated PDF
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @author tunbudi06
     */
    public function submit_temuan(Request $request)
    {
        $data = $request->validate([
            'Id_Temuan' => 'required|int',
            'Id_List_Report' => 'required|int',
            'pdf' => 'required|file',
            'timestamp' => 'required|string',
            'comments' => 'nullable|string',
        ]);

        try {
            return DB::transaction(function () use ($request, $data) {
                $temuan = Temuan::findOrFail($data['Id_Temuan']);
                $jsonData = new JsonHelper($temuan->Object_Temuan);

                $listReport = List_Report::with('report.member')->findOrFail($data['Id_List_Report']);
                $timeReport = Carbon::parse($listReport->report->Start_Report)->format('Y-m-d');
                $current_user = User::where('Id_User', session('Id_User'))->firstOrFail();

                if ($request->hasFile('pdf')) {
                    $pdf = $request->file('pdf');

                    $relativePath = $this->base_path.$current_user->Id_User.'_'.$timeReport.'_'.$listReport->report->member->id;
                    $directory = public_path($relativePath);

                    if (! file_exists($directory)) {
                        if (! mkdir($directory, 0755, true) && ! is_dir($directory)) {
                            throw new \RuntimeException('Failed to create directory: '.$directory);
                        }
                    }

                    $filename = 'TM_'.$temuan->Id_Temuan.' _ '.$listReport->Name_Procedure.'.pdf';

                    if (! $pdf->move($directory, $filename)) {
                        throw new \RuntimeException('Failed to move PDF file');
                    }

                    $jsonData->File_Path_Temuan = $relativePath.'/'.$filename;
                    $jsonData->Submit_Time_Temuan = Carbon::now()->toDateTimeString();
                    $jsonData->Comments_Temuan = json_decode($data['comments'] ?? '', true) ?? '';

                    $temuan->Object_Temuan = $jsonData;
                    $temuan->Time_Temuan = $data['timestamp'];
                    $temuan->save();

                    return response()->json([
                        'success' => true,
                        'message' => 'Temuan berhasil disubmit',
                        'data' => [
                            'Id_Temuan' => $temuan->Id_Temuan,
                            'file_path' => $jsonData->File_Path_Temuan,
                        ],
                    ]);
                }

                return response()->json([
                    'success' => false,
                    'message' => 'No PDF file uploaded',
                ], 400);
            });
        } catch (\Exception $e) {
            Log::error('Failed to submit temuan', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal submit temuan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show list of all temuans for current auditor with optional date filter
     *
     * @return \Illuminate\View\View
     *
     * @author tunbudi06
     */
    public function index(Request $request)
    {
        $page = 'temuan';
        $Id_User = session('Id_User');
        $date = $request->input('date') ?? Carbon::today()->format('Y-m-d');

        $query = Temuan::with(['ListReport.report.member', 'User'])
            ->where('Id_User', $Id_User)
            ->whereNotNull('Time_Temuan')
            ->whereDate('Time_Temuan', $date);

        $temuans = $query->orderBy('Time_Temuan', 'desc')->get();

        return view('auditors.temuan.index', [
            'page' => $page,
            'temuans' => $temuans,
            'date' => $date,
        ]);
    }

    public function show(string $Id_Temuan)
    {
        $page = 'temuan';
        $temuan = Temuan::with(['ListReport.report.member', 'User'])->where('Id_Temuan', $Id_Temuan)->firstOrFail();
        $id_member = $temuan->ListReport->report->member->id;
        $timeReport = Carbon::parse($temuan->ListReport->report->Start_Report)->format('Y-m-d');
        $fullPath = 'storage/reports/'.$timeReport.'_'.$id_member;
        $fileName = $temuan->ListReport->Name_Procedure.'.pdf';
        $pdfPath = $fullPath.'/'.$fileName;

        return view('auditors.temuan.temuan_show', [
            'page' => $page,
            'temuan' => $temuan,
            'pdfPath' => $pdfPath,
        ]);
    }

    public function validateTemuan(Request $request, string $Id_Temuan)
    {
        $data = $request->validate([
            'validation_notes' => 'nullable|string|max:1000',
        ]);

        try {
            return DB::transaction(function () use ($data, $Id_Temuan) {
                $temuan = Temuan::findOrFail($Id_Temuan);
                $jsonData = new JsonHelper($temuan->Object_Temuan);
                $jsonData->Validation_Notes = $data['validation_notes'] ?? '';
                $jsonData->Validation_Time = Carbon::now()->toDateTimeString();

                $temuan->Object_Temuan = $jsonData;
                $temuan->Status_Temuan = 1;
                $temuan->save();

                return redirect()
                    ->back()
                    ->with('success', 'Temuan berhasil divalidasi dan ditandai selesai');
            });
        } catch (\Exception $e) {
            Log::error('Failed to validate temuan', [
                'Id_Temuan' => $Id_Temuan,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->back()
                ->with('error', 'Gagal memvalidasi temuan: '.$e->getMessage());
        }
    }
}
