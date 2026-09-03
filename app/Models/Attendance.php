<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Employee;

class Attendance extends Model
{
    // Table name
    protected $table = 'attendances';

    // Primary key
    protected $primaryKey = 'id';

    // timestamps
    public $timestamps = true;

    // ✅ FULL COMPATIBLE FIELDS (OLD + FACE SYSTEM)
    protected $fillable = [
        'uid',
        'emp_id',

        // legacy system
        'state',
        'status',
        'type',
        'attendance_time',
        'attendance_date',

        // face recognition system
        'check_in_time',
        'check_out_time',
    ];

    // casting
    protected $casts = [
        'attendance_date' => 'date',
    ];

    // relationship
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id');
    }

    public function activeBreakLog()
    {
        return $this->hasOne(BreakLog::class, 'attendance_id')->whereNull('break_end');
    }
}