<?php

namespace App\Http\Controllers\Leader;

use App\Http\Controllers\Controller;
use App\Models\Procedure;
use App\Models\User;
use Carbon\Carbon;

class LeaderController extends Controller
{
    public function index()
    {
        $page = 'dashboard';
        $today = Carbon::today();

        $Id_User = session('Id_User');
        $user = User::find($Id_User);

        $procedures = Procedure::count();
        $missing = Procedure::whereNot('Name_Tractor',"PAINTING")->whereNull('Pic_Procedure')->count();
//        $missing = Procedure::whereNull('Pic_Procedure')->count();

        return view('leaders.dashboard', compact('page', 'today', 'user', 'procedures', 'missing'));
    }
}
