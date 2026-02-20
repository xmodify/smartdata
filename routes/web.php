<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/admin', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/admin/system', [App\Http\Controllers\Admin\StructureController::class, 'index'])->name('admin.system.index');
    Route::post('/admin/git-pull', [App\Http\Controllers\Admin\StructureController::class, 'gitPull'])->name('admin.git_pull');
    Route::post('/admin/upgrade-structure', [App\Http\Controllers\Admin\StructureController::class, 'upgrade'])->name('admin.upgrade_structure');
    Route::put('/admin/sys-var/{sysVar}', [App\Http\Controllers\Admin\StructureController::class, 'update_sysvar'])->name('admin.sys_var.update');

    // Profile Routes
    Route::put('/profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // HOSxP Stats Routes
    Route::get('/hosxp/stats', [App\Http\Controllers\Hosxp\StatsController::class, 'index'])->name('hosxp.stats.index');

    Route::prefix('hosxp/stats')->group(function () {
        Route::get('/top20-opd', [App\Http\Controllers\Hosxp\StatsController::class, 'top20_opd'])->name('hosxp.stats.top20_opd');
        Route::get('/top20-ipd', [App\Http\Controllers\Hosxp\StatsController::class, 'top20_ipd'])->name('hosxp.stats.top20_ipd');
        Route::get('/group-506', [App\Http\Controllers\Hosxp\StatsController::class, 'group_506'])->name('hosxp.stats.group_506');
    });

    // OPD Routes
    Route::prefix('hosxp/opd')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\OpdController::class, 'index'])->name('hosxp.opd.index');
    });

    // IPD Routes
    Route::prefix('hosxp/ipd')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\IpdController::class, 'index'])->name('hosxp.ipd.index');
    });

    // Physic Routes
    Route::prefix('hosxp/physic')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\PhysicController::class, 'index'])->name('hosxp.physic.index');
    });

    // Hmed Routes
    Route::prefix('hosxp/hmed')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\HmedController::class, 'index'])->name('hosxp.hmed.index');
    });

    // Dent Routes
    Route::prefix('hosxp/dent')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\DentController::class, 'index'])->name('hosxp.dent.index');
    });

    // Phar Routes
    Route::prefix('hosxp/phar')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\PharController::class, 'index'])->name('hosxp.phar.index');
    });

    // NCD Routes
    Route::prefix('hosxp/ncd')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\NcdController::class, 'index'])->name('hosxp.ncd.index');
    });

    // PCU Routes
    Route::prefix('hosxp/pcu')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\PcuController::class, 'index'])->name('hosxp.pcu.index');
    });

    // ER Routes
    Route::prefix('hosxp/er')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\ErController::class, 'index'])->name('hosxp.er.index');
    });

    // Refer Routes
    Route::prefix('hosxp/refer')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\ReferController::class, 'index'])->name('hosxp.refer.index');
        Route::get('/refer-out-4h', [App\Http\Controllers\Hosxp\ReferController::class, 'refer_out_4h'])->name('hosxp.refer.refer_out_4h');
        Route::get('/refer-out-24h', [App\Http\Controllers\Hosxp\ReferController::class, 'refer_out_24h'])->name('hosxp.refer.refer_out_24h');
        Route::get('/refer-out-top20', [App\Http\Controllers\Hosxp\ReferController::class, 'refer_out_top20'])->name('hosxp.refer.refer_out_top20');
    });

    // Death Routes
    Route::prefix('hosxp/death')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\DeathController::class, 'index'])->name('hosxp.death.index');
        Route::get('/death-top20', [App\Http\Controllers\Hosxp\DeathController::class, 'death_top20'])->name('hosxp.death.death_top20');
    });

    Route::get('/hosxp/diagnosis', [App\Http\Controllers\Hosxp\DiagnosisController::class, 'index'])->name('hosxp.diagnosis.index');
    Route::match(['get', 'post'], '/hosxp/diagnosis/{type}', [App\Http\Controllers\Hosxp\DiagnosisController::class, 'report'])->name('hosxp.diagnosis.report');

    Route::resource('/admin/users', App\Http\Controllers\Admin\UserController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'show' => 'admin.users.show',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy',
    ]);
});
