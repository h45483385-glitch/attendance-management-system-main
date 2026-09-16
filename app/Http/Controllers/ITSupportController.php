<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ITSupportController extends Controller
{
    /**
     * Show the IT Support dashboard.
     */
    public function index()
    {
        // Return a view for the IT Support dashboard. Create the blade file as needed.
        return view('it-support.dashboard');
    }
}
