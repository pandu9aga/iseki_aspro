<?php

use App\Http\Controllers\Auditor\BaseController;
use App\Http\Controllers\Auditor\ProfileAuditorController;
use App\Http\Controllers\Auditor\ReportAuditorController;
use App\Http\Controllers\Auditor\TemuanAuditorController;
use App\Http\Controllers\Leader\LeaderController;
use App\Http\Controllers\Leader\ProcedureController;
use App\Http\Controllers\Leader\ProfileController;
use App\Http\Controllers\Leader\ReportController;
use App\Http\Controllers\Leader\TeamController;
use App\Http\Controllers\Leader\TemuanLeaderController;
use App\Http\Controllers\Leader\UserController;
use App\Http\Controllers\MainController;
use App\Http\Controllers\Member\HomeController;
use App\Http\Controllers\Member\ProfileMemberController;
use App\Http\Controllers\Member\ReportMemberController;
use App\Http\Middleware\AuditorMiddleware;
use App\Http\Middleware\LeaderMiddleware;
use App\Http\Middleware\MemberMiddleware;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [MainController::class, 'index'])->name('/');
Route::get('/login', [MainController::class, 'index'])->name('login');
Route::post('/login/auth', [MainController::class, 'login'])->name('login.auth');
Route::post('/login/member', [MainController::class, 'login_member'])->name('login.member');
Route::get('/logout', [MainController::class, 'logout'])->name('logout');
Route::get('/logout_member', [MainController::class, 'logout_member'])->name('logout.member');

// =====================
// Leader Routes
// =====================
Route::middleware(LeaderMiddleware::class)->group(function () {
    // Dashboard
    Route::get('/dashboard', [LeaderController::class, 'index'])->name('dashboard');

    // User Management
    Route::prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('user');
        Route::post('/create', [UserController::class, 'create'])->name('user.create');
        Route::put('/update/{Id_User}', [UserController::class, 'update'])->name('user.update');
        Route::delete('/delete/{Id_User}', [UserController::class, 'destroy'])->name('user.destroy');
    });

    // Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'index'])->name('profile');
        Route::put('/update/{Id_User}', [ProfileController::class, 'update'])->name('profile.update');
    });

    // Team & Member Management
    Route::prefix('team')->group(function () {
        Route::get('/', [TeamController::class, 'index'])->name('team');
        Route::post('/member/create', [TeamController::class, 'member_create'])->name('member.create');
        Route::put('/member/update/{Id_Member}', [TeamController::class, 'member_update'])->name('member.update');
        Route::delete('/member/delete/{Id_Member}', [TeamController::class, 'member_destroy'])->name('member.destroy');
        Route::post('/member/import', [TeamController::class, 'member_import'])->name('member.import');
        Route::get('/data', [TeamController::class, 'team_data'])->name('team_data');
        Route::post('/data/create', [TeamController::class, 'team_data_create'])->name('team_data.create');
        Route::put('/data/update/{Id_Team}', [TeamController::class, 'team_data_update'])->name('team_data.update');
        Route::delete('/data/delete/{Id_Team}', [TeamController::class, 'team_data_destroy'])->name('team_data.destroy');
    });

    // Procedure Management
    Route::prefix('procedure/tractor')->group(function () {
        Route::get('/', [ProcedureController::class, 'index'])->name('procedure');
        Route::post('/create', [ProcedureController::class, 'create_tractor'])->name('procedure.tractor.create');
        Route::put('/update/{Id_Tractor}', [ProcedureController::class, 'update_tractor'])->name('procedure.tractor.update');
        Route::delete('/delete/{Id_Tractor}', [ProcedureController::class, 'destroy_tractor'])->name('procedure.tractor.destroy');

        // Area
        Route::get('/area/{Name_Tractor}', [ProcedureController::class, 'index_area'])->name('procedure.area.index');
        Route::post('/area/create', [ProcedureController::class, 'create_area'])->name('procedure.area.create');
        Route::put('/area/update/{Id_Area}', [ProcedureController::class, 'update_area'])->name('procedure.area.update');
        Route::delete('/area/delete/{Id_Area}', [ProcedureController::class, 'destroy_area'])->name('procedure.area.destroy');

        // Procedure
        Route::get('/area/procedure/{Name_Tractor}/{Name_Area}', [ProcedureController::class, 'index_procedure'])->name('procedure.procedure.index');
        Route::post('/area/procedure/create', [ProcedureController::class, 'create_procedure'])->name('procedure.procedure.create');
        Route::post('/area/procedure/item', [ProcedureController::class, 'insert_item_procedure'])->name('procedure.procedure.item');
        Route::put('/area/procedure/update/{Id_Procedure}', [ProcedureController::class, 'update_procedure'])->name('procedure.procedure.update');
        Route::put('/area/procedure/upload/{Id_Procedure}', [ProcedureController::class, 'upload_procedure'])->name('procedure.procedure.upload');
        Route::delete('/area/procedure/delete/{Id_Procedure}', [ProcedureController::class, 'destroy_procedure'])->name('procedure.procedure.destroy');
    });

    Route::prefix('missing')->group(function () {
        Route::get('/', [ProcedureController::class, 'index_missing'])->name('missing');
        Route::get('/area/{Name_Tractor}', [ProcedureController::class, 'index_area_missing'])->name('missing.area.index');
        Route::get('/area/procedure/{Name_Tractor}/{Name_Area}', [ProcedureController::class, 'index_procedure_missing'])->name('missing.procedure.index');
        Route::post('/assign-to-training', [ProcedureController::class, 'assign_to_training'])->name('missing.assign.training');
    });

    // Report Management
    Route::prefix('report')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('report');
        Route::get('/list/{year}/{month}', [ReportController::class, 'reporter'])->name('reporter');
        Route::post('/reporter', [ReportController::class, 'create_reporter'])->name('reporter.create');
        Route::get('/list/{Id_Report}', [ReportController::class, 'list_report'])->name('list_report');
        Route::get('/list/detail/{Id_Report}/{Name_Tractor}', [ReportController::class, 'list_report_detail'])->name('list_report_detail');
        Route::post('/store', [ReportController::class, 'store'])->name('report.store');
        Route::delete('/list/{Id_List_Report}', [ReportController::class, 'destroy_list_report'])->name('list_report.destroy');
        Route::patch('/list/reset/{Id_List_Report}', [ReportController::class, 'reset_list_report'])->name('list_report.reset');
        Route::get('/show/{Id_List_Report}', [ReportController::class, 'report'])->name('report.detail');
        Route::post('/submit/{Id_List_Report}', [ReportController::class, 'submit_report'])->name('report.detail.submit');
        Route::put('/reporter/{id}', [ReportController::class, 'update'])->name('reporter.update');
        Route::delete('/reporter/{id}', [ReportController::class, 'destroy'])->name('reporter.destroy');
        Route::post('/create-template', [ReportController::class, 'createMonthlyTemplate'])->name('report.create.template');
    });

    Route::prefix('temuan')->group(function () {
        Route::prefix('list')->group(function () {
            Route::get('/', [TemuanLeaderController::class, 'index'])->name('leader-temuan.list');
            Route::get('/show/{Id_Temuan}', [TemuanLeaderController::class, 'show'])->name('leader-temuan.show');
            Route::post('penanganan/create', [TemuanLeaderController::class, 'createPenanganan'])->name('leader-temuan.penanganan.create');
            Route::post('penanganan/submit', [TemuanLeaderController::class, 'submitPenanganan'])->name('leader-temuan.penanganan.submit');
        });
        Route::delete('delete/{Id_Temuan}', [TemuanLeaderController::class, 'deleteTemuan'])->name('leader-temuan.delete');
        Route::post('submit-penanganan', [TemuanLeaderController::class, 'submitPenanganan'])->name('leader-temuan.submit-penanganan');
        Route::put('update-tipe/{Id_Temuan}', [TemuanLeaderController::class, 'updateTipeTemuan'])->name('leader-temuan.update-tipe');
        Route::get('statistics/monthly', [TemuanLeaderController::class, 'getMonthlyStatistics'])->name('leader-temuan.statistics.monthly');
        Route::get('statistics/missing', [TemuanLeaderController::class, 'getMissingStatistics'])->name('leader-temuan.statistics.missing');
        Route::get('missing', [TemuanLeaderController::class, 'missingTemuan'])->name('leader-temuan.missing');
    });
});

// =====================
// Member Routes
// =====================
Route::middleware(MemberMiddleware::class)->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::prefix('profile_member')->group(function () {
        Route::get('/', [ProfileMemberController::class, 'index'])->name('profile_member');
        Route::put('/update/{Id_Member}', [ProfileMemberController::class, 'update'])->name('profile_member.update');
    });
    Route::prefix('report_member')->group(function () {
        Route::get('/', [ReportMemberController::class, 'index'])->name('report_member');
        Route::get('/list/{Id_Report}', [ReportMemberController::class, 'report_list_member'])->name('report_list_member');
        Route::get('/list/report/{Id_List_Report}', [ReportMemberController::class, 'detail'])->name('report_list_member.detail');
        Route::post('/list/submit/{Id_List_Report}', [ReportMemberController::class, 'submit_report'])->name('report_list_member.submit');
        Route::post('/list/upload-photos/{Id_List_Report}', [ReportMemberController::class, 'uploadPhotos'])->name('report_list_member.upload_photos');
    });
});

// =====================
// Auditor Routes
// =====================
Route::middleware(AuditorMiddleware::class)->group(function () {
    Route::get('/base', [BaseController::class, 'index'])->name('base');
    Route::prefix('profile_auditor')->group(function () {
        Route::get('/', [ProfileAuditorController::class, 'index'])->name('profile_auditor');
        Route::put('/update/{Id_User}', [ProfileAuditorController::class, 'update'])->name('profile_auditor.update');
    });
    Route::prefix('report_auditor')->group(function () {
        Route::get('/', [ReportAuditorController::class, 'index'])->name('report_auditor');
        // justt add a few sub routing fixed name since this will get bug if only using parameters only
        Route::get('/date/{year}/{month}', [ReportAuditorController::class, 'reporter'])->name('report_auditor.list');
        Route::get('/list/{Id_Report}', [ReportAuditorController::class, 'list_report'])->name('list_report_auditor');
        Route::get('/list/detail/{Id_Report}/{Name_Tractor}', [ReportAuditorController::class, 'list_report_detail'])->name('list_report_detail_auditor');
        Route::get('/report/{Id_List_Report}', [ReportAuditorController::class, 'report'])->name('report_auditor.detail');
        Route::post('/submit/{Id_List_Report}', [ReportAuditorController::class, 'submit_report'])->name('report_auditor.detail.submit');
    });

    Route::prefix('temuan_auditor')->group(function () {
        Route::get('show/{Id_List_Report}', [TemuanAuditorController::class, 'temuan_report'])->name('auditor-report.temuan_report');
        Route::post('auditor/temuan/create', [TemuanAuditorController::class, 'create_temuan'])->name('auditor-report.temuan_create');
        Route::post('auditor/temuan/submit', [TemuanAuditorController::class, 'submit_temuan'])->name('auditor-report.temuan_submit');
        Route::prefix('list')->group(function () {
            Route::get("/", [TemuanAuditorController::class, 'index'])->name('auditor-report.temuan_index');
            Route::get("/show/{Id_Temuan}", [TemuanAuditorController::class, 'show'])->name('auditor-report.temuan_show');
            Route::patch('validate/{Id_Temuan}', [TemuanAuditorController::class, 'validateTemuan'])->name('auditor-temuan.validate');
        });
    });

    Route::get('statistics/monthly', [TemuanAuditorController::class, 'getMonthlyStatistics'])->name('auditor-temuan.statistics.monthly');
    Route::get('statistics/missing', [TemuanAuditorController::class, 'getMissingStatistics'])->name('auditor-temuan.statistics.missing');
    Route::get('missing', [TemuanAuditorController::class, 'missingTemuan'])->name('auditor-temuan.missing');
});
