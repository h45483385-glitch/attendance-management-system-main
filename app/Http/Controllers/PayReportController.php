<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PayReport;

class PayReportController extends Controller
{
    public function index()
    {
        // இதுதான் நாம் டிசைன் செய்த Blade ஃபைலை பிரவுசருக்குக் கொண்டு வரும்
        return view('pay-report.index');
    }
}