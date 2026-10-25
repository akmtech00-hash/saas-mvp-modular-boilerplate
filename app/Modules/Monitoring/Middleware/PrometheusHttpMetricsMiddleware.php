<?php

declare(strict_types=1);

namespace App\Modules\Monitoring\Middleware;

use App\Modules\Monitoring\Services\MetricsCollector;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PrometheusHttpMetricsMiddleware
{
    public function __construct(
        private readonly MetricsCollector $metricsCollector
    ) {}

    /**
     * Measure request latency and record HTTP metrics.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        /** @var Response $response */
        $response = $next($request);

        $duration = microtime(true) - $startTime;
        $route = $request->route()?->getName() ?? $request->path();
        $method = $request->method();
        $statusCode = $response->getStatusCode();

        $this->metricsCollector->recordHttpRequest($method, $route, $statusCode, $duration);

        return $response;
    }
}
