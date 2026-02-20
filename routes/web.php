<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::get('/register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);

Route::get('/', function () {
    if (Auth::check()) {
        if (Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
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
        Route::get('/refer-out', [App\Http\Controllers\Hosxp\StatsController::class, 'refer_out'])->name('hosxp.stats.refer_out');
        Route::get('/refer-out-4h', [App\Http\Controllers\Hosxp\StatsController::class, 'refer_out_4h'])->name('hosxp.stats.refer_out_4h');
        Route::get('/refer-out-24h', [App\Http\Controllers\Hosxp\StatsController::class, 'refer_out_24h'])->name('hosxp.stats.refer_out_24h');
        Route::get('/refer-out-top20', [App\Http\Controllers\Hosxp\StatsController::class, 'refer_out_top20'])->name('hosxp.stats.refer_out_top20');
        Route::get('/death', [App\Http\Controllers\Hosxp\StatsController::class, 'death'])->name('hosxp.stats.death');
        Route::get('/death-top20', [App\Http\Controllers\Hosxp\StatsController::class, 'death_top20'])->name('hosxp.stats.death_top20');
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
