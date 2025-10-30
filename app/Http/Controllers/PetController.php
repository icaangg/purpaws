<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pet;
use Illuminate\Support\Facades\Storage;

class PetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch only pets marked as 'Adoptable' and paginate them
        $pets = Pet::where('status', 'Adoptable')->latest()->paginate(12);

        // Pass the fetched pets to the view
        return view('pets.index', compact('pets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('pets.show', compact('pet'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
    public function reportLost()
    {
        // This will load the view at resources/views/lost/report.blade.php
        return view('lost.report');
    }
    public function storeLostReport(Request $request)
    {
        // ⚠️ Placeholder: You would put form validation and saving logic here.
        // For now, let's just redirect back.

        // Example validation (you will need to adjust your Pet model and migration later)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'required|in:Dog,Cat,Other',
            'last_seen_location' => 'required|string|max:255',
            'contact_email' => 'required|email',
            'photo' => 'nullable|image|max:2048',
        ]);
        
        // Logic to save the pet with status 'Lost' goes here...

        return redirect()->route('lost.report')->with('success', 'Thank you! Your lost pet report has been submitted and is pending verification.');

    }
    public function indexLostAndFound()
    {
        // 1. Fetch pets that have been reported as LOST
        // We order by the newest reports first.
        $lostPets = Pet::where('status', 'Lost')
                    ->latest()
                    ->paginate(9);
        
        // 2. Fetch pets that have been reported as FOUND (Placeholder for future feature)
        // Note: You will need to create a 'Found' status in your migration later, 
        // and a form for users to report finding a pet.
        $foundPets = Pet::where('status', 'Found') 
                         ->latest()
                         ->paginate(9); 
                         
        // If you haven't added 'Found' to your migration, this query will fail.
        // For now, let's pass an empty collection for $foundPets if needed.
        // Let's assume the user will have a separate 'Found' model later, or update status.
        // For simplicity, we will pass $lostPets and a separate list for now.

        return view('lost.index', compact('lostPets', 'foundPets'));
    }
    public function reportFound()
    {
        // This will load the view at resources/views/lost/found_report.blade.php
        return view('lost.found_report');
    }

    /**
     * Store a newly reported found pet in the database.
     */
    public function storeFoundReport(Request $request)
    {
        // ⚠️ Placeholder: You would put form validation and saving logic here.
        // The saved pet will initially have the status 'Found'.

        $validated = $request->validate([
            'species' => 'required|in:Dog,Cat,Other',
            'found_location' => 'required|string|max:255',
            'contact_email' => 'required|email',
            'photo' => 'required|image|max:2048', // Photo is crucial for found pets
        ]);
        
        // Logic to save the pet with status 'Found' goes here...

        return redirect()->route('found.report')->with('success', 'Thank you! Your found pet report has been submitted and is pending verification.');
    }
    /**
     * Finds potential matches for a newly reported pet.
     * @param \App\Models\Pet $pet The newly created pet report (Lost or Found).
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function matchPet(Pet $pet)
    {
        // Determine the opposite status to search for
        $searchStatus = ($pet->status === 'Lost') ? 'Found' : 'Lost';

        $query = Pet::query();

        // 1. Filter by the OPPOSITE STATUS
        $query->where('status', $searchStatus);

        // 2. Filter by SPECIES (Required match)
        $query->where('species', $pet->species);

        // 3. Filter by BREED (Strong match, if available)
        if ($pet->breed) {
            // Use 'orWhere' inside a closure to group conditions
            $query->where(function ($q) use ($pet) {
                // Check if the breed matches exactly OR if the breed is null (allowing for matches against unknowns)
                $q->where('breed', $pet->breed)
                  ->orWhereNull('breed'); 
            });
        }
        
        // 4. Filter by COLOR (Good match)
        if ($pet->color) {
             // Basic implementation: check if the color strings are similar (e.g., using LIKE)
             // A better solution would use keywords, but LIKE is simple for now.
             $query->where('color', 'LIKE', '%' . $pet->color . '%');
        }

        // Return the first 5 potential matches
        return $query->limit(5)->get();
    }
    /**
     * Show the form for owners to register their pet.
     */
    public function registerPet()
    {
        // This will load the view at resources/views/pets/register.blade.php
        return view('pets.register');
    }

    /**
     * Store a new pet registration by an owner.
     */
    public function storeRegistration(Request $request)
    {
        // 1. Validation for registration form (including required vaccination proof)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'species' => 'required|in:Dog,Cat,Other',
            'breed' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'pet_image' => 'required|image|max:4096', // Image is required for registration
            'contact_email' => 'required|email',
            'vaccination_record' => 'nullable|file|mimes:pdf,jpg,png|max:4096', // Scope: vaccination records 
        ]);
        
        // 2. Handle File Uploads
        $imagePath = $request->file('pet_image')->store('registered_pets/photos', 'public');
        $vaccinationPath = null;
        if ($request->hasFile('vaccination_record')) {
            $vaccinationPath = $request->file('vaccination_record')->store('registered_pets/vaccines', 'public');
        }

        // 3. Save the Pet with status 'Registered'
        Pet::create(array_merge($validated, [
            'status' => 'Registered',
            'pet_image' => $imagePath,
            'vaccination_record' => $vaccinationPath,
        ]));

        return redirect()->route('pet.register')->with('success', 'Thank you! Your pet has been successfully registered with BantayPurrPaws. Your registration is pending administrator verification.');
    }
}