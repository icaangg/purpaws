<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pet extends Model
{
    use HasFactory;

    // These are the fields from your migration and forms that can be mass-assigned
    protected $fillable = [
        'name',
        'species',
        'breed',
        'color',             // <-- For matching 
        'description',
        'status',            // <-- Crucial for matching (Lost, Found)
        'pet_image',
        'contact_email',     // <-- Added for reports
        'last_seen_location', // <-- Added for lost reports
        'found_location',    // <-- Added for found reports
    ];
}
