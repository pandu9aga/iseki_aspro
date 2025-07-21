<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Tractor;
use App\Models\Procedure;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index(){
        $page = "dashboard";
        $today = Carbon::today();

        $Id_User = session('Id_User');
        $user = User::find($Id_User);

        $tractors = Tractor::count();
        $procedures = Procedure::count();
        $users = User::count();

        return view('admins.dashboard', compact('page', 'today', 'user', 'tractors', 'procedures', 'users'));
    }    
}
