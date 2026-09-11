<?php

namespace App\Http\Controllers\Leader;

use App\Exports\AuditExport;
use App\Http\Controllers\Controller;
use App\Models\List_Report;
use App\Models\List_Training;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class AuditController extends Controller
{
    public function index($year = null, $month = null)
    {
        $page = 'audit';
        $year = $year ?? date('Y');
        $month = $month ?? date('m');

        $auditors = User::where('Id_Type_User', 1)->get();

        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

        $auditStats = [];

        foreach ($auditors as $auditor) {
            $reportCounts = List_Report::where('Auditor_Name', $auditor->Username_User)
                ->whereYear('Time_Approved_Auditor', $year)
                ->whereMonth('Time_Approved_Auditor', $month)
                ->selectRaw('DAY(Time_Approved_Auditor) as day, count(*) as count')
                ->groupBy('day')
                ->pluck('count', 'day');

            $trainingCounts = List_Training::where('Auditor_Name', $auditor->Username_User)
                ->whereYear('Time_Approved_Auditor', $year)
                ->whereMonth('Time_Approved_Auditor', $month)
                ->selectRaw('DAY(Time_Approved_Auditor) as day, count(*) as count')
                ->groupBy('day')
                ->pluck('count', 'day');

            $dailyCounts = [];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dailyCounts[$day] = ($reportCounts[$day] ?? 0) + ($trainingCounts[$day] ?? 0);
            }

            $auditStats[] = [
                'name' => $auditor->Username_User,
                'counts' => $dailyCounts,
                'total' => array_sum($dailyCounts),
            ];
        }

        // Handle audits with unknown/null Auditor_Name
        $unknownReportCounts = List_Report::whereNull('Auditor_Name')
            ->whereNotNull('Time_Approved_Auditor')
            ->whereYear('Time_Approved_Auditor', $year)
            ->whereMonth('Time_Approved_Auditor', $month)
            ->selectRaw('DAY(Time_Approved_Auditor) as day, count(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day');

        $unknownTrainingCounts = List_Training::whereNull('Auditor_Name')
            ->whereNotNull('Time_Approved_Auditor')
            ->whereYear('Time_Approved_Auditor', $year)
            ->whereMonth('Time_Approved_Auditor', $month)
            ->selectRaw('DAY(Time_Approved_Auditor) as day, count(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day');

        $unknownCounts = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $unknownCounts[$day] = ($unknownReportCounts[$day] ?? 0) + ($unknownTrainingCounts[$day] ?? 0);
        }

        if (array_sum($unknownCounts) > 0) {
            $auditStats[] = [
                'name' => 'Unknown Auditor',
                'counts' => $unknownCounts,
                'total' => array_sum($unknownCounts),
            ];
        }

        return view('leaders.audits.index', compact('page', 'auditStats', 'year', 'month', 'daysInMonth'));
    }

    public function detail($year, $month, $day, $auditorName)
    {
        $page = 'audit';
        $date = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');

        $reportQuery = List_Report::with('report')
            ->whereDate('Time_Approved_Auditor', $date);

        $trainingQuery = List_Training::with('training')
            ->whereDate('Time_Approved_Auditor', $date);

        if ($auditorName === 'Unknown Auditor') {
            $reportQuery->whereNull('Auditor_Name');
            $trainingQuery->whereNull('Auditor_Name');
        } else {
            $reportQuery->where('Auditor_Name', $auditorName);
            $trainingQuery->where('Auditor_Name', $auditorName);
        }

        $reportAudits = $reportQuery->get()->map(function ($item) {
            $item->audit_type = 'Jobdesc';
            return $item;
        });

        $trainingAudits = $trainingQuery->get()->map(function ($item) {
            $item->audit_type = 'Training';
            return $item;
        });

        $audits = $reportAudits->concat($trainingAudits)->sortBy('Time_Approved_Auditor')->values();

        return view('leaders.audits.detail', compact('page', 'audits', 'date', 'auditorName'));
    }

    public function exportExcel($year, $month)
    {
        $fileName = 'Audit_Report_'.$year.'_'.$month.'.xlsx';

        return Excel::download(new AuditExport($year, $month), $fileName);
    }
}
