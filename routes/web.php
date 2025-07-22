<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\LeaderMiddleware;
use App\Http\Middleware\MemberMiddleware;
use App\Http\Controllers\MainController;
use App\Http\Controllers\Leader\LeaderController;
use App\Http\Controllers\Leader\UserController;
use App\Http\Controllers\Leader\ProfileController;
use App\Http\Controllers\Leader\ProcedureController;
use App\Http\Controllers\Member\HomeController;
use App\Http\Controllers\Member\ProfileMemberController;
use App\Http\Controllers\Member\ReportMemberController;

Route::get('/', [MainController::class, 'index'])->name('/');
Route::get('/login', [MainController::class, 'index'])->name('login');
Route::post('/login/auth', [MainController::class, 'login'])->name('login.auth');
Route::get('/logout', [MainController::class, 'logout'])->name('logout');

Route::middleware(LeaderMiddleware::class)->group(function () {
    Route::get('/dashboard', [LeaderController::class, 'index'])->name('dashboard');

    Route::get('/user', [UserController::class, 'index'])->name('user');
    Route::post('/user/create', [UserController::class, 'create'])->name('user.create');
    Route::put('/user/update/{Id_User}', [UserController::class, 'update'])->name('user.update');
    Route::delete('/user/delete/{Id_User}', [UserController::class, 'destroy'])->name('user.destroy');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile/update/{Id_User}', [ProfileController::class, 'update'])->name('profile.update');

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
});

Route::middleware(MemberMiddleware::class)->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/profile_member', [ProfileMemberController::class, 'index'])->name('profile_member');
    Route::put('/profile_member/update/{Id_User}', [ProfileMemberController::class, 'update'])->name('profile_member.update');

    Route::get('/report_member', [ReportMemberController::class, 'index'])->name('report_member');
    Route::get('/get-areas/{Name_Tractor}', function($Name_Tractor) {
        $areas = \App\Models\Area::where('Name_Tractor', $Name_Tractor)->orderBy('Name_Area')->get(['Name_Area']);
        return response()->json($areas);
    });
    Route::post('/report_member/store', [ReportMemberController::class, 'store_report'])->name('report_member.store');
    Route::get('/report_list_member/{Id_Report}', [ReportMemberController::class, 'report_list_member'])->name('report_list_member');
    Route::get('/report_list_member/report/{Id_List_Report}', [ReportMemberController::class, 'pdfEditor'])->name('report_list_member.pdf.editor');
    Route::post('/report_list_member/report/save/{Id_List_Report}', [ReportMemberController::class, 'savePdfEditor'])->name('report_list_member.pdf.editor.save');
    Route::post('/report_list_member/report/submit/{Id_List_Report}', [ReportMemberController::class, 'submitReport'])->name('report_list_member.pdf.editor.submit');
});