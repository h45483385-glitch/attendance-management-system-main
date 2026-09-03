<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FingerDevices extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        "name",
        "ip",
        "serialNumber",
        "device_id",
        "type",
        "location",
        "status",
        "last_seen",
        "token",
        "registered_by",
    ];
}
