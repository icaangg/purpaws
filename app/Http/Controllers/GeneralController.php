<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class GeneralController extends Controller
{
    public function about()
    {
        return view('about'); 
    }

    public function donate()
    {
        return view('donate'); 
    }
    
    public function volunteer()
    {
        return view('volunteer'); 
    }
}   