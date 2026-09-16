<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebAuthnCredential extends Model
{
    protected $table = 'webauthn_credentials';

    protected $fillable = [
        'user_id',
        'credential_id',
        'public_key',
        'counter',
        'device_name'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
