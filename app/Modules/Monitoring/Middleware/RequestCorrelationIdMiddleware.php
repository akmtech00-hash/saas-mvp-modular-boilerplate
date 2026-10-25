<?php

declare(strict_types=1);

namespace App\Modules\Monitoring\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RequestCorrelationIdMiddleware
{
    /**
     * Handle incoming request by assigning and propagating a unique correlation ID.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $correlationId = $request->header('X-Correlation-Id') 
            ?? $request->header('X-Request-Id') 
            ?? (string) Str::uuid();

        // Attach correlation ID to request attributes
        $request->attributes->set('correlation_id', $correlationId);

        // Share with Monolog context for all subsequent logs in this request cycle
        Log::shareContext([
            'correlation_id' => $correlationId,
            'ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 100),
        ]);

        /** @var Response $response */
        $response = $next($request);

        // Propagate tracing headers in downstream response
        $response->headers->set('X-Correlation-Id', $correlationId);
        $response->headers->set('X-Request-Id', $correlationId);

        return $response;
    }
}
