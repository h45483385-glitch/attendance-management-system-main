<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyPayLog extends Model
{
    use HasFactory;

    protected $table = 'daily_pay_logs';

    protected $fillable = [
        'employee_id',
        'date',
        'scheduled_minutes',
        'worked_minutes',
        'overtime_minutes',
        'daily_wage_rate',
        'regular_pay',
        'shortfall_deduction',
        'overtime_pay',
        'overtime_status',
        'admin_remarks',
        'final_day_pay'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}