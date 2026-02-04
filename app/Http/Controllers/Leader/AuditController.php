<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\List_Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $dailyCounts = [];
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
                $count = List_Report::where('Auditor_Name', $auditor->Username_User)
                    ->whereDate('Time_Approved_Auditor', $date)
                    ->count();
                $dailyCounts[$day] = $count;
            }
            
            $auditStats[] = [
                'name' => $auditor->Username_User,
                'counts' => $dailyCounts,
                'total' => array_sum($dailyCounts)
            ];
        }

        // Handle audits with unknown/null Auditor_Name
        $unknownCounts = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
            $count = List_Report::whereNull('Auditor_Name')
                ->whereNotNull('Time_Approved_Auditor')
                ->whereDate('Time_Approved_Auditor', $date)
                ->count();
            $unknownCounts[$day] = $count;
        }
        
        if (array_sum($unknownCounts) > 0) {
            $auditStats[] = [
                'name' => 'Unknown Auditor',
                'counts' => $unknownCounts,
                'total' => array_sum($unknownCounts)
            ];
        }

        return view('leaders.audits.index', compact('page', 'auditStats', 'year', 'month', 'daysInMonth'));
    }

    public function detail($year, $month, $day, $auditorName)
    {
        $page = 'audit';
        $date = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
        
        $query = List_Report::with('report.member')
            ->whereDate('Time_Approved_Auditor', $date);

        if ($auditorName === 'Unknown Auditor') {
            $query->whereNull('Auditor_Name');
        } else {
            $query->where('Auditor_Name', $auditorName);
        }

        $audits = $query->get();

        return view('leaders.audits.detail', compact('page', 'audits', 'date', 'auditorName'));
    }
}
