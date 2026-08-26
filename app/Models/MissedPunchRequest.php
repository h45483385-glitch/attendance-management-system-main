<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MissedPunchRequest extends Model
{
    use HasFactory;

    protected $table = 'missed_punch_requests';

    protected $fillable = [
        'emp_id',
        'date',
        'recorded_time',
        'reason',
        'requested_timeout',
        'status',
    ];

    // Employee உடன் உறவு (Relationship)
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_id', 'id');
    }
}