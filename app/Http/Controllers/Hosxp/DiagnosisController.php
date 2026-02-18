<?php

namespace App\Http\Controllers\Hosxp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DiagnosisController extends Controller
{
    /**
     * Display the HOSxP Diagnosis Index.
     */
    public function index()
    {
        return view('hosxp.diagnosis.index');
    }
}
