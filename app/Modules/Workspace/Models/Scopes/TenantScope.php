<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Models\Scopes;

use App\Modules\Workspace\Services\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $tenantContext = app(TenantContext::class);

        if ($tenantContext->hasWorkspace()) {
            $builder->where($model->getTable() . '.workspace_id', '=', $tenantContext->getWorkspaceId());
        }
    }
}
