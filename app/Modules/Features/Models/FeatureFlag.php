<?php

declare(strict_types=1);

namespace App\Modules\Features\Models;

use App\Modules\Workspace\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureFlag extends Model
{
    use HasFactory;

    protected $table = 'feature_flags';

    protected $fillable = [
        'workspace_id',
        'feature_key',
        'is_enabled',
        'rules',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'rules' => 'array',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class, 'workspace_id');
    }
}
