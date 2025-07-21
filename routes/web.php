<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\AuthMiddleware;
use App\Http\Controllers\MainController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ProcedureController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\ProfileUserController;
use App\Http\Controllers\User\ReportUserController;

Route::get('/', [MainController::class, 'index'])->name('/');
Route::get('/login', [MainController::class, 'index'])->name('login');
Route::post('/login/auth', [MainController::class, 'login'])->name('login.auth');
Route::get('/logout', [MainController::class, 'logout'])->name('logout');

Route::middleware(AdminMiddleware::class)->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

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

Route::middleware(AuthMiddleware::class)->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::get('/profile_user', [ProfileUserController::class, 'index'])->name('profile_user');
    Route::put('/profile_user/update/{Id_User}', [ProfileUserController::class, 'update'])->name('profile_user.update');

    Route::get('/report_user', [ReportUserController::class, 'index'])->name('report_user');
    Route::get('/get-areas/{Name_Tractor}', function($Name_Tractor) {
        $areas = \App\Models\Area::where('Name_Tractor', $Name_Tractor)->orderBy('Name_Area')->get(['Name_Area']);
        return response()->json($areas);
    });
    Route::post('/report_user/store', [ReportUserController::class, 'store_report'])->name('report_user.store');
    Route::get('/report_list_user/{Id_Report}', [ReportUserController::class, 'report_list_user'])->name('report_list_user');
    Route::get('/report_list_user/report/{Id_List_Report}', [ReportUserController::class, 'pdfEditor'])->name('report_list_user.pdf.editor');
    Route::post('/report_list_user/report/save/{Id_List_Report}', [ReportUserController::class, 'savePdfEditor'])->name('report_list_user.pdf.editor.save');
    Route::post('/report_list_user/report/submit/{Id_List_Report}', [ReportUserController::class, 'submitReport'])->name('report_list_user.pdf.editor.submit');
});