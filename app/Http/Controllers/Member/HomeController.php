<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\List_Report;
use App\Models\List_Training;
use App\Helpers\MemberHelper;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        $page = 'home';
        $today = Carbon::today();

        $nik = session('NIK_Member');
        $memberIds = MemberHelper::getLinkedIds($nik);
        $member = MemberHelper::findByNik(session('NIK_Member'));

        $reports = List_Report::with('report')
            ->whereHas('report', function ($query) use ($memberIds) {
                $query->whereIn('Id_Member', $memberIds);
            })
            ->count();

        $trainings = List_Training::with('training')
            ->whereHas('training', function ($query) use ($memberIds) {
                $query->whereIn('Id_Member', $memberIds);
            })
            ->count();

        return view('members.home', compact('page', 'today', 'member', 'reports', 'trainings'));
    }
}
