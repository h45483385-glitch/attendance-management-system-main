<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreakLog extends Model
{
    protected $fillable = [
        'attendance_id', 
        'emp_id', 
        'break_start', 
        'break_end', 
        'duration_minutes'
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}