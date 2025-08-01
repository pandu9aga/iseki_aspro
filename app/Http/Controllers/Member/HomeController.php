<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Member;
use App\Models\Report;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index(){
        $page = "home";
        $today = Carbon::today();

        $Id_Member = session('Id_Member');
        $member = Member::find($Id_Member);

        $reports = Report::where('Id_Member', $Id_Member)->count();

        return view('members.home', compact('page', 'today', 'member', 'reports'));
    }    
}
