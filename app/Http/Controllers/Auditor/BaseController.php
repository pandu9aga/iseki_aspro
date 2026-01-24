<?php

namespace App\Http\Controllers\Auditor;

use App\Http\Controllers\Controller;
use App\Models\Procedure;
use App\Models\User;
use Carbon\Carbon;

class BaseController extends Controller
{
    public function index()
    {
        $page = 'home';
        $today = Carbon::today();

        $Id_User = session('Id_User');
        $user = User::find($Id_User);

        $procedures = Procedure::count();

        return view('auditors.home', compact('page', 'today', 'user', 'procedures'));
    }
}
