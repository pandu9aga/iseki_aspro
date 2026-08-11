<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\List_Training;
use App\Helpers\MemberHelper;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TrainingMemberController extends Controller
{
    public function index()
    {
        $page = 'training';

        $nik = session('NIK_Member');
        $memberIds = MemberHelper::getLinkedIds($nik);
        $reports = Training::whereIn('Id_Member', $memberIds)
            ->orderBy('Start_Training', 'desc')
            ->get();
        $member = MemberHelper::findByNik(session('NIK_Member'));

        return view('members.trainings.index', compact('page', 'reports', 'member'));
    }

    public function report_list_member($Id_Training)
    {
        $page = 'training';

        $report = Training::findOrFail($Id_Training);
        $list_reports = List_Training::where('Id_Training', $Id_Training)->orderBy('Name_Procedure', 'asc')->get();
        $member = MemberHelper::findByNik(session('NIK_Member'));

        return view('members.trainings.list_report', compact('page', 'report', 'list_reports', 'member'));
    }

    public function detail($Id_List_Training)
    {
        $page = 'training';

        $member = MemberHelper::findByNik(session('NIK_Member'));

        $listReport = List_Training::with('training')->findOrFail($Id_List_Training);

        $id_member = $listReport->training->member->Id_Member;
        $timeReport = Carbon::parse($listReport->training->Start_Training)->format('Y-m-d');

        $fullPath = 'storage/trainings/'.$timeReport.'_'.$id_member;

        $fileName = $listReport->Name_Procedure.'.pdf';
        $pdfPath = $fullPath.'/'.$fileName;

        // Get sibling list trainings for prev/next navigation (all procedures in the training)
        $siblingReports = List_Training::where('Id_Training', $listReport->Id_Training)
            ->orderBy('Name_Tractor')
            ->orderBy('Name_Procedure')
            ->pluck('Id_List_Training')
            ->toArray();

        $currentIndex = array_search($Id_List_Training, $siblingReports);
        $prevReportId = ($currentIndex !== false && $currentIndex > 0) ? $siblingReports[$currentIndex - 1] : null;
        $nextReportId = ($currentIndex !== false && $currentIndex < count($siblingReports) - 1) ? $siblingReports[$currentIndex + 1] : null;
        $currentPos = $currentIndex !== false ? $currentIndex + 1 : 0;

        return view('members.trainings.report', compact(
            'page', 'listReport', 'pdfPath', 'member',
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

            // Update waktu
            $listReport->Time_List_Report = $request->input('timestamp');
            $listReport->save();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 400);
    }
}
