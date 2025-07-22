<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Report;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index(){
        $page = "home";
        $today = Carbon::today();

        $Id_User = session('Id_User');
        $user = User::find($Id_User);

        $reports = Report::where('Id_User', $Id_User)->count();

        return view('members.home', compact('page', 'today', 'user', 'reports'));
    }    
}
