<?php

declare(strict_types=1);

namespace App\Modules\Audit\Services;

use App\Modules\Audit\Models\AuditLog;
use App\Modules\Workspace\Services\TenantContext;

class AuditLogger
{
    /**
     * Record an audit log entry for a tenant action.
     */
    public function log(
        string $event,
        array $newValues = [],
        array $oldValues = [],
        ?string $auditableType = null,
        ?string $auditableId = null,
        ?string $workspaceId = null,
        ?int $userId = null
    ): AuditLog {
        $resolvedWorkspaceId = $workspaceId ?? app(TenantContext::class)->getWorkspaceId();
        $resolvedUserId = $userId ?? auth()->id();

        return AuditLog::create([
            'workspace_id' => $resolvedWorkspaceId,
            'user_id' => $resolvedUserId,
            'event' => $event,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 255),
        ]);
    }
}
