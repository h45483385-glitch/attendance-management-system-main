<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    
    // எந்த எரரும் வராமல் எல்லா காலம்களையும் அப்டேட் செய்ய அனுமதி
    protected $guarded = []; 
}