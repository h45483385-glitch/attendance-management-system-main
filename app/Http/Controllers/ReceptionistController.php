<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReceptionistController extends Controller
{
    /**
     * Show the Receptionist dashboard.
     */
    public function index()
    {
        // Return a view for the Receptionist dashboard. Create the blade file as needed.
        return view('receptionist.dashboard');
    }
}
