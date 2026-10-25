<?php

declare(strict_types=1);

namespace App\Modules\Auth\Models;

use App\Modules\Workspace\Models\Workspace;
use App\Modules\Workspace\Models\WorkspaceMember;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_super_admin' => 'boolean',
    ];

    public function memberships(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class, 'user_id');
    }

    public function ownedWorkspaces(): HasMany
    {
        return $this->hasMany(Workspace::class, 'owner_id');
    }

    /**
     * Get all workspaces the user has membership in.
     */
    public function workspaces()
    {
        return $this->belongsToMany(
            Workspace::class,
            'workspace_members',
            'user_id',
            'workspace_id'
        )->withPivot('role')->withTimestamps();
    }
}
