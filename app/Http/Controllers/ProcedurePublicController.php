<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Procedure;
use App\Models\Tractor;
use Illuminate\Http\Request;

class ProcedurePublicController extends Controller
{
    public function index()
    {
        $page = 'procedure';
        $tractors = Tractor::orderBy('Name_Tractor', 'asc')->get();

        $tractorProcedureCounts = [];
        foreach ($tractors as $tractor) {
            $tractorProcedureCounts[$tractor->Name_Tractor] = Procedure::where('Name_Tractor', $tractor->Name_Tractor)->count();
        }

        return view('public.procedures.index', compact('page', 'tractors', 'tractorProcedureCounts'));
    }

    public function index_area($Name_Tractor)
    {
        $page = 'procedure';
        $tractor = $Name_Tractor;
        $photoTractor = Tractor::where('Name_Tractor', $Name_Tractor)->value('Photo_Tractor');
        $areas = Area::where('Name_Tractor', $Name_Tractor)->orderBy('Name_Area', 'asc')->get();

        return view('public.procedures.areas', compact('page', 'tractor', 'photoTractor', 'areas'));
    }

    public function index_procedure($Name_Tractor, $Name_Area)
    {
        $page = 'procedure';
        $tractor = $Name_Tractor;
        $photoTractor = Tractor::where('Name_Tractor', $Name_Tractor)->value('Photo_Tractor');
        $area = $Name_Area;
        $procedures = Procedure::where('Name_Tractor', $Name_Tractor)
            ->where('Name_Area', $Name_Area)
            ->orderBy('Name_Procedure', 'asc')
            ->get();

        return view('public.procedures.procedures', compact('page', 'tractor', 'photoTractor', 'area', 'procedures'));
    }
}
