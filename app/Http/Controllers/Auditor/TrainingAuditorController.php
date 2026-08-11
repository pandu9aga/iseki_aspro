<?php

namespace App\Http\Controllers\Auditor;

use App\Http\Controllers\Controller;
use App\Models\List_Training;
use App\Helpers\MemberHelper;
use App\Models\Procedure;
use App\Models\Training;
use App\Models\Tractor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrainingAuditorController extends Controller
{
    public function index()
    {
        $page = 'training';

        return view('auditors.trainings.index', compact('page'));
    }

    public function reporter($year, $month)
    {
        $page = 'training';

        $reports = Training::whereYear('Start_Training', $year)
            ->whereMonth('Start_Training', $month)
            ->orderBy('Start_Training')
            ->get();

        $reportDate = Carbon::createFromDate($year, $month, 1)->format('Y-m-d');
        $members = MemberHelper::getAllMembers($reportDate);

        return view('auditors.trainings.reporter', compact('page', 'reports', 'members', 'year', 'month'));
    }

    public function list_report(string $Id_Training)
    {
        $page = 'training';

        $report = Training::where('Id_Training', $Id_Training)->first();
        $tractors = Tractor::select('Name_Tractor', 'Photo_Tractor')
            ->distinct()
            ->orderBy('Name_Tractor')
            ->get();

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

        return view('auditors.trainings.list_report', compact('page', 'report', 'tractorReports', 'Id_Training'));
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

        return view('auditors.trainings.list_report_detail', compact('page', 'report', 'list_reports', 'procedures', 'Id_Training', 'tractor'));
    }

    public function report($Id_List_Training)
    {
        $page = 'training';

        $Id_User = session('Id_User');
        $user = User::where('Id_User', $Id_User)->first();

        $listReport = List_Training::with('training')->findOrFail($Id_List_Training);

        $id_member = $listReport->training->member->Id_Member;
        $timeReport = Carbon::parse($listReport->training->Start_Training)->format('Y-m-d');

        $fullPath = 'storage/trainings/'.$timeReport.'_'.$id_member;

        $fileName = $listReport->Name_Procedure.'.pdf';
        $pdfPath = $fullPath.'/'.$fileName;

        // Get sibling list trainings for prev/next navigation (all tractors)
        $siblingReports = List_Training::where('Id_Training', $listReport->Id_Training)
            ->orderBy('Name_Tractor')
            ->orderBy('Name_Procedure')
            ->pluck('Id_List_Training')
            ->toArray();

        $currentIndex = array_search($Id_List_Training, $siblingReports);
        $prevReportId = ($currentIndex !== false && $currentIndex > 0) ? $siblingReports[$currentIndex - 1] : null;
        $nextReportId = ($currentIndex !== false && $currentIndex < count($siblingReports) - 1) ? $siblingReports[$currentIndex + 1] : null;
        $currentPos = $currentIndex !== false ? $currentIndex + 1 : 0;

        return view('auditors.trainings.report', compact(
            'page', 'listReport', 'pdfPath', 'user',
            'prevReportId', 'nextReportId',
            'currentPos', 'siblingReports'
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

            // Update waktu and Auditor Name
            $listReport->Time_Approved_Auditor = $request->input('timestamp');
            $listReport->Auditor_Name = session('Username_User');
            $listReport->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }

    public function destroy_list_report($Id_List_Training)
    {
        $listReport = List_Training::with('training')->findOrFail($Id_List_Training);

        // Ambil info untuk path folder
        $id_member = $listReport->training->Id_Member;
        $startTraining = Carbon::parse($listReport->training->Start_Training)->format('Y-m-d');
        $pdfPath = "trainings/{$startTraining}_{$id_member}/{$listReport->Name_Procedure}.pdf";

        // Hapus file PDF jika ada
        if (Storage::disk('public')->exists($pdfPath)) {
            Storage::disk('public')->delete($pdfPath);
        }

        // Hapus dari database
        $listReport->delete();

        return redirect()->back()->with('success', 'Prosedur berhasil dihapus dari training.');
    }

    public function duplicate_report($Id_List_Training)
    {
        $listReport = List_Training::with(['training'])->findOrFail($Id_List_Training);

        // Find a unique name
        $baseName = preg_replace('/ - Retrain \d+$/', '', $listReport->Name_Procedure);

        $count = List_Training::where('Id_Training', $listReport->Id_Training)
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
        $id_member = $listReport->training->Id_Member;
        $timeReport = Carbon::parse($listReport->training->Start_Training)->format('Y-m-d');
        $targetFolder = "trainings/{$timeReport}_{$id_member}";
        $destPdf = "{$targetFolder}/{$newName}.pdf";

        if (Storage::disk('public')->exists($originalBlankPdf)) {
            Storage::disk('public')->copy($originalBlankPdf, $destPdf);
        }

        return redirect()->route('list_training_detail_auditor', [
            'Id_Training' => $listReport->Id_Training,
            'Name_Tractor' => $listReport->Name_Tractor,
        ])->with('success', 'Training member berhasil diduplikat.');
    }
}
