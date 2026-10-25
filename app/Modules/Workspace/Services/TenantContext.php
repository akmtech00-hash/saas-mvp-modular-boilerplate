<?php

declare(strict_types=1);

namespace App\Modules\Workspace\Services;

use App\Modules\Workspace\Models\Workspace;
use Illuminate\Support\Facades\Log;

class TenantContext
{
    private ?Workspace $currentWorkspace = null;

    /**
     * Set the current active workspace tenant.
     */
    public function setWorkspace(Workspace $workspace): void
    {
        $this->currentWorkspace = $workspace;

        // Enrich structured logging context with tenant information
        Log::shareContext([
            'workspace_id' => $workspace->id,
            'workspace_slug' => $workspace->slug,
        ]);
    }

    /**
     * Get the active workspace.
     */
    public function getWorkspace(): ?Workspace
    {
        return $this->currentWorkspace;
    }

    /**
     * Get the active workspace UUID, if set.
     */
    public function getWorkspaceId(): ?string
    {
        return $this->currentWorkspace?->id;
    }

    /**
     * Check if a workspace context is currently bound.
     */
    public function hasWorkspace(): bool
    {
        return $this->currentWorkspace !== null;
    }

    /**
     * Clear the tenant context (useful for queue jobs or testing).
     */
    public function clear(): void
    {
        $this->currentWorkspace = null;
    }
}
