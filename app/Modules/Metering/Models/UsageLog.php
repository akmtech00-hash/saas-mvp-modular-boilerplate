<?php

declare(strict_types=1);

namespace App\Modules\Metering\Models;

use App\Modules\Workspace\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsageLog extends Model
{
    use HasFactory;

    protected $table = 'usage_logs';

    protected $fillable = [
        'workspace_id',
        'user_id',
        'metric_name',
        'amount',
        'operation',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'amount' => 'integer',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new \App\Modules\Workspace\Models\Scopes\TenantScope());
    }
}
