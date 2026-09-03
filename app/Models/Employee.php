<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'employees';

    protected $fillable = [
        'name',
        'email',
        'pin_code',
        'position',
        'department',
        'role',
        'schedule_id',
        'face_enrolled',
        'face_photo_path',
        'fingerprint_enrolled',
        'employment_type',
        'status'
    ];

    protected $hidden = [
        'pin_code',
        'remember_token',
    ];

    // ❌ IMPORTANT FIX: use ID (NOT name)
    public function getRouteKeyName()
    {
        return 'id';
    }

    /**
     * Accessor for schedule_id attribute.
     */
    public function getScheduleIdAttribute()
    {
        return $this->schedules()->first()?->id;
    }

    /**
     * Mutator for schedule_id to sync pivot table.
     */
    public function setScheduleIdAttribute($value)
    {
        if ($value) {
            if ($this->exists) {
                $this->schedules()->sync([$value]);
            } else {
                static::saved(function ($employee) use ($value) {
                    $employee->schedules()->sync([$value]);
                });
            }
        }
    }

    // =====================
    // RELATIONSHIPS
    // =====================

    public function check()
    {
        return $this->hasMany(Check::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }

    public function latetime()
    {
        return $this->hasMany(Latetime::class);
    }

    public function leave()
    {
        return $this->hasMany(Leave::class);
    }

    public function overtime()
    {
        return $this->hasMany(Overtime::class);
    }

    public function schedules()
    {
        return $this->belongsToMany(
            'App\Models\Schedule',
            'schedule_employees',
            'emp_id',
            'schedule_id'
        );
    }
}