<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SysVar;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $users = User::all();
        $sysVars = SysVar::all();
        return view('admin.dashboard', compact('users', 'sysVars'));
    }
}
