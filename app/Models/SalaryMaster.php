<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryMaster extends Model
{
    use HasFactory;

    protected $table = 'salary_masters';

    protected $fillable = [
        'department',
        'designation',
        'current_base_salary',
        'scheduled_salary',
        'effective_date'
    ];
}