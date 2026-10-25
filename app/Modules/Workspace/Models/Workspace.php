<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Models;

use App\Modules\Billing\Models\Subscription;
use App\Modules\Metering\Models\UsageMeter;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Workspace extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'workspaces';

    protected $fillable = [
        'name',
        'slug',
        'owner_id',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(WorkspaceMember::class, 'workspace_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'workspace_id');
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class, 'workspace_id')
            ->whereIn('stripe_status', ['active', 'trialing'])
            ->where(function ($query) {
                $query->whereNull('ends_at')
                      ->orWhere('ends_at', '>', now());
            });
    }

    public function usageMeters(): HasMany
    {
        return $this->hasMany(UsageMeter::class, 'workspace_id');
    }

    public function hasUserRole(int $userId, array|string $roles): bool
    {
        $roles = (array) $roles;
        return $this->members()
            ->where('user_id', $userId)
            ->whereIn('role', $roles)
            ->exists();
    }
}
