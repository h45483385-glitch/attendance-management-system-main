<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    public function getRouteKeyName()
    {
        return 'name';
    }

    /**
     * Role relationship
     */
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'role_users',
            'user_id',
            'role_id'
        );
    }

    /**
     * Check multiple roles
     */
    public function hasAnyRole($roles)
    {
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }
            return false;
        }

        return $this->hasRole($roles);
    }

    /**
     * SAFE role check (prevents crash)
     */
    public static function hasRole($role)
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        $firstRole = $user->roles()->first();

        if (!$firstRole) {
            return false;
        }

        return $firstRole->slug === $role;
    }

    /**
     * Alternative safe method (recommended for future use)
     */
    public function hasRoleSafe($role)
    {
        return $this->roles()->where('slug', $role)->exists();
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission($permission)
    {
        $role = $this->roles()->first();
        if (!$role) {
            return false;
        }

        // Super Admin / Admin role has all permissions
        if ($role->slug === 'admin') {
            return true;
        }

        $perms = $role->permissions;
        if (is_array($perms)) {
            if (in_array($permission, $perms)) {
                return true;
            }

            $parts = explode('.', $permission);
            if (count($parts) > 0) {
                $wildcard = $parts[0] . '.*';
                if (in_array($wildcard, $perms)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Fillable fields
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'pin_code',
        'status',
        'last_login_at',
        'last_login_ip',
        'failed_logins',
        'locked_until',
    ];

    /**
     * Hidden fields
     */
    protected $hidden = [
        'pin_code',
        'password',
        'remember_token',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}