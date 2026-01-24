<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\List_Report;
use App\Models\Member;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        $page = 'home';
        $today = Carbon::today();

        $Id_Member = session('Id_Member');
        $member = Member::find($Id_Member);

        $reports = List_Report::with('report')
            ->whereHas('report', function ($query) use ($Id_Member) {
                $query->where('Id_Member', $Id_Member);
            })
            ->count();

        return view('members.home', compact('page', 'today', 'member', 'reports'));
    }
}
