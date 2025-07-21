<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Report;
use App\Models\User;
use App\Models\Tractor;
use App\Models\Procedure;
use App\Models\List_Report;

class ReportUserController extends Controller
{
    public function index()
    {
        $page = "report";

        $Id_User = session('Id_User');
        $reports = Report::where('Id_User', $Id_User)->orderBy('Time_Report', 'desc')->get();

        $tractors = Tractor::orderBy('Name_Tractor')->get();

        return view('users.reports.index', compact('page', 'reports', 'tractors'));
    }

    public function store_report(Request $request)
    {
        // Validasi input
        $request->validate([
            'Name_Tractor' => 'required',
            'Name_Area' => 'required'
        ]);

        $Name_Tractor = $request->input('Name_Tractor');
        $Name_Area = $request->input('Name_Area');
        $userId = session('Id_User'); // Sesuaikan jika login

        // Insert ke reports
        $report = Report::create([
            'Id_User' => $userId,
            'Name_Area' => $Name_Area,
            'Name_Tractor' => $Name_Tractor,
            'Time_Report' => now()
        ]);

        // Buat folder
        Storage::disk('public')->makeDirectory('reports/' . $report->Id_Report);

        // Ambil procedures sesuai tractor dan area
        $procedures = Procedure::where('Name_Tractor', $Name_Tractor)
            ->where('Name_Area', $Name_Area)
            ->get();

        if ($procedures->count() > 0) {
            // Ambil nama user dari relasi belongsTo user()
            $userName = $report->user->Name_User ?? 'UnknownUser';

            // Data list_reports untuk batch insert
            $data = [];

            foreach ($procedures as $procedure) {
                // Tambahkan ke array untuk insert
                $data[] = [
                    'Id_Report' => $report->Id_Report,
                    'Name_Procedure' => $procedure->Name_Procedure,
                    'Name_Area' => $Name_Area,
                    'Name_Tractor' => $Name_Tractor,
                    'Item_Procedure' => $procedure->Item_Procedure,
                    'Time_List_Report' => null,
                    'Time_Approvement' => null,
                    'Reporter_Name' => $userName
                ];

                // Buat folder untuk tiap procedure
                $procedureFolder = 'reports/' . $report->Id_Report . '/' . $procedure->Name_Procedure;
                Storage::disk('public')->makeDirectory($procedureFolder);

                // Path asal file
                $sourcePath = 'procedures/' . $Name_Tractor . '/' . $Name_Area . '/' . $procedure->Name_Procedure . '.pdf';

                // Path tujuan
                $targetName = $report->Id_Report . '-' . $procedure->Name_Procedure . '-' . $userName . '.pdf';
                $targetPath = $procedureFolder . '/' . $targetName;

                // Copy dan rename jika file asal ada
                if (Storage::disk('public')->exists($sourcePath)) {
                    Storage::disk('public')->copy($sourcePath, $targetPath);
                }
            }

            // Insert ke list_reports
            List_Report::insert($data);
        }

        return redirect()->route('report_user')->with('success', 'Report dan list berhasil dibuat');
    }

    public function report_list_user($Id_Report){
        $page = "report";

        $list_reports = List_Report::where('Id_Report', $Id_Report)->orderBy('Name_Procedure', 'asc')->get();

        return view('users.reports.list_report', compact('page', 'list_reports'));
    }

    public function pdfEditor($Id_List_Report)
    {
        $page = "report";

        $Id_User = session('Id_User');
        $user = User::where('Id_User', $Id_User)->first();

        $listReport = List_Report::with('report.user')->findOrFail($Id_List_Report);

        $fileName = $listReport->Id_Report . '-' . $listReport->Name_Procedure . '-' . $listReport->report->user->Name_User . '.pdf';
        $pdfPath = 'storage/reports/' . $listReport->Id_Report . '/' . $listReport->Name_Procedure. '/' . $fileName;

        return view('users.reports.report', compact('page', 'listReport', 'pdfPath', 'user'));
    }

    public function savePdfEditor(Request $request, $Id_List_Report)
    {
        // Contoh menerima data JSON dari canvas editor
        $data = $request->input('canvasData');

        // Lakukan render ulang PDF atau simpan JSON untuk diproses
        // (di sini kamu bisa pakai library seperti dompdf, TCPDF, snappy, atau client-side pdf-lib / pdf.js)
        
        // Simulasi: redirect dengan success
        return redirect()->route('report_list_user.pdf.editor', ['Id_List_Report' => $Id_List_Report])->with('success', 'PDF updated successfully');
    }

    public function submitReport(Request $request, $Id_List_Report)
    {
        $listReport = List_Report::findOrFail($Id_List_Report);

        if ($request->hasFile('pdf')) {
            $pdf = $request->file('pdf');

            // Path target di public/storage/reports/...
            $path = 'storage/reports/' . $listReport->Id_Report . '/' . $listReport->Name_Procedure . '/';
            $filename = $listReport->Id_Report . '-' . $listReport->Name_Procedure . '-' . $listReport->Reporter_Name . '.pdf';

            // Pastikan direktori ada
            $fullPath = public_path($path);
            if (!file_exists($fullPath)) {
                mkdir($fullPath, 0755, true);
            }

            // Pindahkan file ke public/storage/reports/...
            $pdf->move($fullPath, $filename);

            // Update waktu
            $listReport->Time_List_Report = $request->input('timestamp');
            $listReport->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }

}
