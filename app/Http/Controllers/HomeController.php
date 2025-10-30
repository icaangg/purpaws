<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pet; // Import the Pet Model

class HomeController extends Controller 
{
    /**
     * Show the application dashboard (the homepage).
     */
    public function index()
    {
        // 1. Fetch data for the featured pets
        // Grab the last 3 pets added (these will be empty until you run migrations and seed data).
        $featuredPets = Pet::latest()->limit(3)->get(); 

        // 2. Return the view (home.blade.php) and pass the data
        return view('home', [
            // The 'featuredPets' key becomes the $featuredPets variable in your blade file
            'featuredPets' => $featuredPets,
        ]);
    }
}