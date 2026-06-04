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

// Moph Notify Service Routes (Public for Task Scheduler)
Route::post('/moph-notify/night', [App\Http\Controllers\MophNotify\ServiceController::class, 'service_night']);
Route::post('/moph-notify/morning', [App\Http\Controllers\MophNotify\ServiceController::class, 'service_morning']);
Route::post('/moph-notify/afternoon', [App\Http\Controllers\MophNotify\ServiceController::class, 'service_afternoon']);

Route::middleware(['auth'])->group(function () {
    Route::get('/admin', [App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/admin/system', [App\Http\Controllers\Admin\StructureController::class, 'index'])->name('admin.system.index');
    Route::post('/admin/git-pull', [App\Http\Controllers\Admin\StructureController::class, 'gitPull'])->name('admin.git_pull');
    Route::post('/admin/upgrade-structure', [App\Http\Controllers\Admin\StructureController::class, 'upgrade'])->name('admin.upgrade_structure');
    Route::put('/admin/sys-var/{sysVar}', [App\Http\Controllers\Admin\StructureController::class, 'update_sysvar'])->name('admin.sys_var.update');
    
    // Moph Notify Routes
    Route::post('/admin/moph-notify', [App\Http\Controllers\Admin\StructureController::class, 'store_moph_notify'])->name('admin.moph_notify.store');
    Route::put('/admin/moph-notify/{mophNotify}', [App\Http\Controllers\Admin\StructureController::class, 'update_moph_notify'])->name('admin.moph_notify.update');
    Route::delete('/admin/moph-notify/{mophNotify}', [App\Http\Controllers\Admin\StructureController::class, 'destroy_moph_notify'])->name('admin.moph_notify.destroy');
    
    // Telegram Notify Routes
    Route::post('/admin/telegram-notify', [App\Http\Controllers\Admin\StructureController::class, 'store_telegram_notify'])->name('admin.telegram_notify.store');
    Route::put('/admin/telegram-notify/{telegramNotify}', [App\Http\Controllers\Admin\StructureController::class, 'update_telegram_notify'])->name('admin.telegram_notify.update');
    Route::delete('/admin/telegram-notify/{telegramNotify}', [App\Http\Controllers\Admin\StructureController::class, 'destroy_telegram_notify'])->name('admin.telegram_notify.destroy');

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
        Route::get('/wait-time', [App\Http\Controllers\Hosxp\OpdController::class, 'waitTime'])->name('hosxp.opd.wait_time');
        Route::get('/telehealth', [App\Http\Controllers\Hosxp\OpdController::class, 'telehealth'])->name('hosxp.opd.telehealth');
    });

    // IPD Routes
    Route::prefix('hosxp/ipd')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\IpdController::class, 'index'])->name('hosxp.ipd.index');
        Route::get('/severity', [App\Http\Controllers\Hosxp\IpdController::class, 'severity'])->name('hosxp.ipd.severity');
        Route::get('/readmit', [App\Http\Controllers\Hosxp\IpdController::class, 'readmit'])->name('hosxp.ipd.readmit');
    });

    // ICU Routes
    Route::prefix('hosxp/icu')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\IcuController::class, 'index'])->name('hosxp.icu.index');
    });

    // Physic Routes
    Route::prefix('hosxp/physic')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\PhysicController::class, 'index'])->name('hosxp.physic.index');
        Route::get('/service-stats', [App\Http\Controllers\Hosxp\PhysicController::class, 'service_stats'])->name('hosxp.physic.service_stats');
        Route::get('/top20-diag', [App\Http\Controllers\Hosxp\PhysicController::class, 'top20_diag'])->name('hosxp.physic.top20_diag');
        Route::get('/service-value', [App\Http\Controllers\Hosxp\PhysicController::class, 'service_value'])->name('hosxp.physic.service_value');
    });

    // Hmed Routes
    Route::prefix('hosxp/hmed')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\HmedController::class, 'index'])->name('hosxp.hmed.index');
        Route::get('/service-stats', [App\Http\Controllers\Hosxp\HmedController::class, 'service_stats'])->name('hosxp.hmed.service_stats');
        Route::get('/top20-diag', [App\Http\Controllers\Hosxp\HmedController::class, 'top20_diag'])->name('hosxp.hmed.top20_diag');
        Route::get('/service-value', [App\Http\Controllers\Hosxp\HmedController::class, 'service_value'])->name('hosxp.hmed.service_value');
    });

    // Dent Routes
    Route::prefix('hosxp/dent')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\DentController::class, 'index'])->name('hosxp.dent.index');
        Route::get('/service-stats', [App\Http\Controllers\Hosxp\DentController::class, 'service_stats'])->name('hosxp.dent.service_stats');
        Route::get('/top20-diag', [App\Http\Controllers\Hosxp\DentController::class, 'top20_diag'])->name('hosxp.dent.top20_diag');
        Route::get('/service-value', [App\Http\Controllers\Hosxp\DentController::class, 'service_value'])->name('hosxp.dent.service_value');
    });

    // Phar Routes
    Route::prefix('hosxp/phar')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\PharController::class, 'index'])->name('hosxp.phar.index');
        Route::get('/top20-value', [App\Http\Controllers\Hosxp\PharController::class, 'top20_value'])->name('hosxp.phar.top20_value');
        Route::get('/prescription-count', [App\Http\Controllers\Hosxp\PharController::class, 'prescription_count'])->name('hosxp.phar.prescription_count');
        Route::get('/top20-diag', [App\Http\Controllers\Hosxp\PharController::class, 'top20_diag'])->name('hosxp.phar.top20_diag');
    });

    // NCD Routes
    Route::prefix('hosxp/ncd')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\NcdController::class, 'index'])->name('hosxp.ncd.index');
        Route::get('/register/{clinic_code}', [App\Http\Controllers\Hosxp\NcdController::class, 'clinic_register'])->name('hosxp.ncd.clinic_register');
        Route::get('/dm-register', function() {
            return redirect()->route('hosxp.ncd.clinic_register', ['clinic_code' => '001']);
        })->name('hosxp.ncd.dm_register');
    });

    // PCU Routes
    Route::prefix('hosxp/pcu')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\PcuController::class, 'index'])->name('hosxp.pcu.index');
    });

    // CT Scan Routes
    Route::prefix('hosxp/ct_scan')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\CtScanController::class, 'index'])->name('hosxp.ct_scan.index');
        Route::get('/print', [App\Http\Controllers\Hosxp\CtScanController::class, 'print'])->name('hosxp.ct_scan.print');
    });

    // ER Routes
    Route::prefix('hosxp/er')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\ErController::class, 'index'])->name('hosxp.er.index');
    });

    // Refer Routes
    Route::prefix('hosxp/refer')->group(function () {
        Route::get('/', [App\Http\Controllers\Hosxp\ReferController::class, 'index'])->name('hosxp.refer.index');
        Route::get('/refer-in', [App\Http\Controllers\Hosxp\ReferController::class, 'refer_in'])->name('hosxp.refer.refer_in');
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

    Route::resource('/skpcard', App\Http\Controllers\Smartdata\SkpcardController::class)->names([
        'index' => 'skpcard.index',
        'create' => 'skpcard.create',
        'store' => 'skpcard.store',
        'update' => 'skpcard.update',
        'destroy' => 'skpcard.destroy',
    ]);

    // ─── ศูนย์ยืม-คืน ────────────────────────────────────────────────
    Route::prefix('lend')->group(function () {
        Route::get('/',                [App\Http\Controllers\Smartdata\LendController::class, 'index'])->name('lend.index');
        Route::get('/create',          [App\Http\Controllers\Smartdata\LendController::class, 'create'])->name('lend.create');
        Route::post('/',               [App\Http\Controllers\Smartdata\LendController::class, 'store'])->name('lend.store');
        Route::get('/settings',        [App\Http\Controllers\Smartdata\LendItemController::class, 'index'])->name('lend.settings');
        Route::post('/settings',       [App\Http\Controllers\Smartdata\LendItemController::class, 'store'])->name('lend.settings.store');
        Route::put('/settings/{id}',   [App\Http\Controllers\Smartdata\LendItemController::class, 'update'])->name('lend.settings.update');
        Route::put('/settings/{id}/toggle', [App\Http\Controllers\Smartdata\LendItemController::class, 'toggleActive'])->name('lend.settings.toggle');
        Route::put('/{id}',            [App\Http\Controllers\Smartdata\LendController::class, 'update'])->name('lend.update');
        Route::put('/{id}/return',     [App\Http\Controllers\Smartdata\LendController::class, 'processReturn'])->name('lend.process_return');
        Route::put('/{id}/cancel',     [App\Http\Controllers\Smartdata\LendController::class, 'cancel'])->name('lend.cancel');
        Route::get('/{id}/print',      [App\Http\Controllers\Smartdata\LendController::class, 'printForm'])->name('lend.print');
    });

    Route::put('/admin/users/{user}/reset-password', [App\Http\Controllers\Admin\UserController::class, 'resetPassword'])->name('admin.users.reset_password');
    Route::resource('/admin/users', App\Http\Controllers\Admin\UserController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'show' => 'admin.users.show',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy',
    ]);

    // Backoffice HRD Routes
    Route::get('/backoffice/hrd', [App\Http\Controllers\Backoffice\HrdController::class, 'index'])->name('backoffice.hrd.index');
    Route::get('/backoffice/hrd/pdf/summary/{id}', [App\Http\Controllers\Backoffice\HrdController::class, 'checkin_indiv_pdf'])->name('backoffice.hrd.pdf.summary');
    Route::get('/backoffice/hrd/pdf/detail/{id}', [App\Http\Controllers\Backoffice\HrdController::class, 'checkin_indiv_detail_pdf'])->name('backoffice.hrd.pdf.detail');

    // Backoffice Incident Routes
    Route::prefix('backoffice/incident')->group(function () {
        Route::get('/', [App\Http\Controllers\Backoffice\IncidentController::class, 'index'])->name('backoffice.incident.index');
        Route::get('/med_error', [App\Http\Controllers\Backoffice\IncidentController::class, 'med_error'])->name('backoffice.incident.med_error');
        Route::get('/nrls_dataset', [App\Http\Controllers\Backoffice\IncidentController::class, 'nrls_dataset'])->name('backoffice.incident.nrls_dataset');
        Route::get('/nrls_dataset_export', [App\Http\Controllers\Backoffice\IncidentController::class, 'nrls_dataset_export'])->name('backoffice.incident.nrls_dataset_export');
        Route::get('/nrls', [App\Http\Controllers\Backoffice\IncidentController::class, 'nrls'])->name('backoffice.incident.nrls');
        Route::get('/nrls_export', [App\Http\Controllers\Backoffice\IncidentController::class, 'nrls_export'])->name('backoffice.incident.nrls_export');
        Route::get('/nrls_edit', [App\Http\Controllers\Backoffice\IncidentController::class, 'nrls_edit'])->name('backoffice.incident.nrls_edit');
        Route::get('/nrls_editexport', [App\Http\Controllers\Backoffice\IncidentController::class, 'nrls_editexport'])->name('backoffice.incident.nrls_editexport');
        Route::get('/program_detail/{id}', [App\Http\Controllers\Backoffice\IncidentController::class, 'program_detail'])->name('backoffice.incident.program_detail');
        Route::get('/matrix_detail/{type}_{consequence}_{likelihood}', [App\Http\Controllers\Backoffice\IncidentController::class, 'matrix_detail'])->name('backoffice.incident.matrix_detail');
        Route::get('/table_detail', [App\Http\Controllers\Backoffice\IncidentController::class, 'table_detail'])->name('backoffice.incident.table_detail');
    });
});

