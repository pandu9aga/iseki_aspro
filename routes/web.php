<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\LeaderMiddleware;
use App\Http\Middleware\MemberMiddleware;
use App\Http\Controllers\MainController;
use App\Http\Controllers\Leader\LeaderController;
use App\Http\Controllers\Leader\UserController;
use App\Http\Controllers\Leader\TeamController;
use App\Http\Controllers\Leader\ReportController;
use App\Http\Controllers\Leader\ProfileController;
use App\Http\Controllers\Leader\ProcedureController;
use App\Http\Controllers\Member\HomeController;
use App\Http\Controllers\Member\ProfileMemberController;
use App\Http\Controllers\Member\ReportMemberController;

Route::get('/', [MainController::class, 'index'])->name('/');
Route::get('/login', [MainController::class, 'index'])->name('login');
Route::post('/login/auth', [MainController::class, 'login'])->name('login.auth');
Route::post('/login/member', [MainController::class, 'login_member'])->name('login.member');
Route::get('/logout', [MainController::class, 'logout'])->name('logout');
Route::get('/logout_member', [MainController::class, 'logout_member'])->name('logout.member');

Route::middleware(LeaderMiddleware::class)->group(function () {
    Route::get('/dashboard', [LeaderController::class, 'index'])->name('dashboard');

    Route::get('/user', [UserController::class, 'index'])->name('user');
    Route::post('/user/create', [UserController::class, 'create'])->name('user.create');
    Route::put('/user/update/{Id_User}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/user/delete/{Id_User}', [UserController::class, 'destroy'])->name('user.destroy');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile/update/{Id_User}', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/team', [TeamController::class, 'index'])->name('team');
    Route::post('/member/create', [TeamController::class, 'member_create'])->name('member.create');
    Route::put('/member/update/{Id_Member}', [TeamController::class, 'member_update'])->name('member.update');
    Route::delete('/member/delete/{Id_Member}', [TeamController::class, 'member_destroy'])->name('member.destroy');
    Route::post('/member/import', [TeamController::class, 'member_import'])->name('member.import');
    Route::get('/team_data', [TeamController::class, 'team_data'])->name('team_data');
    Route::post('/team_data/create', [TeamController::class, 'team_data_create'])->name('team_data.create');
    Route::put('/team_data/update/{Id_Team}', [TeamController::class, 'team_data_update'])->name('team_data.update');
    Route::delete('/team_data/delete/{Id_Team}', [TeamController::class, 'team_data_destroy'])->name('team_data.destroy');

    Route::get('/procedure/tractor', [ProcedureController::class, 'index'])->name('procedure');
    Route::post('/procedure/tractor/create', [ProcedureController::class, 'create_tractor'])->name('procedure.tractor.create');
    Route::put('/procedure/tractor/update/{Id_Tractor}', [ProcedureController::class, 'update_tractor'])->name('procedure.tractor.update');
    Route::delete('/procedure/tractor/delete/{Id_Tractor}', [ProcedureController::class, 'destroy_tractor'])->name('procedure.tractor.destroy');

    Route::get('/procedure/tractor/area/{Name_Tractor}', [ProcedureController::class, 'index_area'])->name('procedure.area.index');
    Route::post('/procedure/tractor/area/create', [ProcedureController::class, 'create_area'])->name('procedure.area.create');
    Route::put('/procedure/tractor/area/update/{Id_Area}', [ProcedureController::class, 'update_area'])->name('procedure.area.update');
    Route::delete('/procedure/tractor/area/delete/{Id_Area}', [ProcedureController::class, 'destroy_area'])->name('procedure.area.destroy');

    Route::get('/procedure/tractor/area/procedure/{Name_Tractor}/{Name_Area}', [ProcedureController::class, 'index_procedure'])->name('procedure.procedure.index');
    Route::post('/procedure/tractor/area/procedure/create', [ProcedureController::class, 'create_procedure'])->name('procedure.procedure.create');
    Route::post('/procedure/tractor/area/procedure/item', [ProcedureController::class, 'insert_item_procedure'])->name('procedure.procedure.item');
    Route::put('/procedure/tractor/area/procedure/update/{Id_Procedure}', [ProcedureController::class, 'update_procedure'])->name('procedure.procedure.update');
    Route::put('/procedure/tractor/area/procedure/upload/{Id_Procedure}', [ProcedureController::class, 'upload_procedure'])->name('procedure.procedure.upload');
    Route::delete('/procedure/tractor/area/procedure/delete/{Id_Procedure}', [ProcedureController::class, 'destroy_procedure'])->name('procedure.procedure.destroy');

    Route::get('/report', [ReportController::class, 'index'])->name('report');
    Route::get('/process/{Id_Team}', [ReportController::class, 'process'])->name('process');
    Route::post('/process', [ReportController::class, 'create_process'])->name('process.create');
    Route::get('/reporter/{Id_Process}', [ReportController::class, 'reporter'])->name('reporter');
    Route::post('/reporter', [ReportController::class, 'create_reporter'])->name('reporter.create');
    Route::get('/list_report/{Id_Report}', [ReportController::class, 'list_report'])->name('list_report');
    Route::post('/report/store', [ReportController::class, 'store'])->name('report.store');
    Route::get('/report/{Id_List_Report}', [ReportController::class, 'report'])->name('report.detail');
    Route::post('/report/submit/{Id_List_Report}', [ReportController::class, 'submit_report'])->name('report.detail.submit');
});

Route::middleware(MemberMiddleware::class)->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/profile_member', [ProfileMemberController::class, 'index'])->name('profile_member');
    Route::put('/profile_member/update/{Id_Member}', [ProfileMemberController::class, 'update'])->name('profile_member.update');

    Route::get('/report_member', [ReportMemberController::class, 'index'])->name('report_member');
    Route::get('/report_list_member/{Id_Report}', [ReportMemberController::class, 'report_list_member'])->name('report_list_member');
    Route::get('/report_list_member/report/{Id_List_Report}', [ReportMemberController::class, 'detail'])->name('report_list_member.detail');
    Route::post('/report_list_member/submit/{Id_List_Report}', [ReportMemberController::class, 'submit_report'])->name('report_list_member.submit');
});