<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class MonitoringTest extends TestCase
{
    public function test_liveness_check_returns_ok(): void
    {
        $response = $this->get('/health/live');

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'alive',
        ]);
        $this->assertNotNull($response->headers->get('X-Correlation-Id'));
    }

    public function test_readiness_check_returns_structured_health(): void
    {
        $response = $this->get('/health/ready');

        $this->assertContains($response->getStatusCode(), [200, 503]);
        $response->assertJsonStructure([
            'status',
            'timestamp',
            'checks' => [
                'database',
                'redis',
                'storage',
            ],
        ]);
    }

    public function test_prometheus_metrics_endpoint_exports_telemetry(): void
    {
        $response = $this->get('/metrics');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/plain', $response->headers->get('Content-Type'));
        $content = $response->getContent();

        $this->assertStringContainsString('saas_platform_php_memory_bytes', $content);
        $this->assertStringContainsString('saas_platform_workspaces_total', $content);
        $this->assertStringContainsString('saas_platform_http_requests_total', $content);
    }

    public function test_correlation_id_is_propagated_in_response_headers(): void
    {
        $customCorrelationId = 'custom-test-trace-12345';
        $response = $this->withHeader('X-Correlation-Id', $customCorrelationId)->get('/health/live');

        $response->assertStatus(200);
        $this->assertEquals($customCorrelationId, $response->headers->get('X-Correlation-Id'));
    }
}
