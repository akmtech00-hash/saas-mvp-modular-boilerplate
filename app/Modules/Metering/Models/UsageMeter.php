<?php

declare(strict_types=1);

namespace App\Modules\Metering\Models;

use App\Modules\Workspace\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageMeter extends Model
{
    use HasFactory;

    protected $table = 'usage_meters';

    protected $fillable = [
        'workspace_id',
        'metric_name',
        'allocated_limit',
        'current_usage',
        'cycle_resets_at',
    ];

    protected $casts = [
        'cycle_resets_at' => 'datetime',
        'allocated_limit' => 'integer',
        'current_usage' => 'integer',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Modules\Workspace\Models\Scopes\TenantScope());
    }

    public function remainingCredits(): int
    {
        return max(0, $this->allocated_limit - $this->current_usage);
    }
}
