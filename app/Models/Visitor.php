<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    // These are the ONLY fields Laravel allows to be saved
    protected $fillable = [
        'name', 
        'company', 
        'person_to_meet', 
        'purpose',    // Must match DB column name
        'phone',      // Must match DB column name
        'id_type',    // Must match DB column name
        'id_number',  // Must match DB column name
        'check_in_time', 
        'check_out_time', 
        'status'
    ];
}