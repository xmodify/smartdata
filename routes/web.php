<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/admin', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::post('/admin/git-pull', function (Illuminate\Http\Request $request) {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $details = $request->input('details');
        if ($details) {
            \Illuminate\Support\Facades\Log::info('Git Pull triggered by ' . auth()->user()->name . ' with details: ' . $details);
        }

        $output = shell_exec('git pull 2>&1');
        return back()->with('git_output', $output);
    })->name('admin.git_pull');
});
