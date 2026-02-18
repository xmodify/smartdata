<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StatsController extends Controller
{
    /**
     * Display the HOSxP Stats Index.
     */
    public function index()
    {
        return view('hosxp.stats.index');
    }
}
