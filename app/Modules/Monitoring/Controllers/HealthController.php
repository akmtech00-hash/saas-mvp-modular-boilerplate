<?php

declare(strict_types=1);

namespace App\Modules\Monitoring\Controllers;

use App\Modules\Monitoring\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

class HealthController extends Controller
{
    public function __construct(
        private readonly HealthCheckService $healthService
    ) {}

    /**
     * Overall application health and dependency readiness check.
     * Route: GET /health or GET /health/ready
     */
    public function ready(): JsonResponse
    {
        $report = $this->healthService->checkReadiness();
        $statusCode = ($report['status'] === 'healthy') ? 200 : 503;

        return response()->json($report, $statusCode);
    }

    /**
     * Shallow liveness check for Kubernetes/AWS load balancers.
     * Route: GET /health/live
     */
    public function live(): JsonResponse
    {
        return response()->json($this->healthService->checkLiveness(), 200);
    }
}
