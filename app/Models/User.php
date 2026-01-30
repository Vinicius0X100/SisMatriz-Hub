<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'user',
        'email',
        'password',
        'rule',
        'status',
        'is_visible',
        'hide_name',
        'is_pass_change',
        'login_attempts',
        'last_attempt',
        'avatar',
        'accepted_photo',
        'paroquia_id',
        'created_at',
        'timezone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'last_attempt' => 'datetime',
            'created_at' => 'date',
        ];
    }

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function paroquia()
    {
        return $this->belongsTo(ParoquiaSuperadmin::class, 'paroquia_id');
    }

    /**
     * Get the user's roles as an array.
     *
     * @return array
     */
    public function getRolesAttribute()
    {
        return $this->rule ? explode(',', str_replace(' ', '', $this->rule)) : [];
    }

    /**
     * Check if the user has a specific role.
     *
     * @param string|int $role
     * @return bool
     */
    public function hasRole($role)
    {
        return in_array((string)$role, $this->roles);
    }

    /**
     * Check if the user has any of the given roles.
     *
     * @param array $roles
     * @return bool
     */
    public function hasAnyRole(array $roles)
    {
        return !empty(array_intersect($roles, $this->roles));
    }

    /**
     * Get accessible modules for the user.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAccessibleModules()
    {
        $modules = config('modules');
        $userRoles = $this->roles;

        // Super Admin Check
        if ($this->hasAnyRole(['1', '111'])) {
            return collect($modules);
        }

        return collect($modules)->filter(function ($module) use ($userRoles) {
            $allowedRoles = $module['allowed_roles'] ?? [];
            
            // Allow if wildcard present
            if (in_array('*', $allowedRoles)) {
                return true;
            }

            // Allow if user has any of the allowed roles
            if (!empty(array_intersect($userRoles, $allowedRoles))) {
                return true;
            }

            return false;
        });
    }

    public function blockedUsers()
    {
        return $this->hasMany(BlockedUser::class, 'user_id');
    }
}
