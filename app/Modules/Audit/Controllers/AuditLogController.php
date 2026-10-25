<?php

declare(strict_types=1);

namespace App\Modules\Audit\Controllers;

use App\Modules\Audit\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuditLogController extends Controller
{
    /**
     * List audit log entries for the active workspace.
     */
    public function index(Request $request): JsonResponse
    {
        $limit = min(100, max(10, (int) $request->query('limit', 25)));

        $logs = AuditLog::with('user:id,name,email')
            ->latest()
            ->paginate($limit);

        return response()->json($logs);
    }
}
