<?php

declare(strict_types=1);

namespace App\Modules\Monitoring\Controllers;

use App\Modules\Monitoring\Services\MetricsCollector;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class MetricsController extends Controller
{
    public function __construct(
        private readonly MetricsCollector $metricsCollector
    ) {}

    /**
     * Expose Prometheus metrics endpoint.
     * Route: GET /metrics
     */
    public function index(Request $request): Response
    {
        $configuredToken = config('monitoring.prometheus.bearer_token');
        if ($configuredToken) {
            $bearer = $request->bearerToken();
            if ($bearer !== $configuredToken) {
                return new Response('Unauthorized metrics request', 401, ['Content-Type' => 'text/plain']);
            }
        }

        $prometheusData = $this->metricsCollector->exportPrometheus();

        return new Response($prometheusData, 200, [
            'Content-Type' => 'text/plain; version=0.0.4; charset=utf-8',
        ]);
    }
}
