<?php

declare(strict_types=1);

namespace App\Modules\Webhooks\Services;

use App\Modules\Webhooks\Models\WebhookDelivery;
use App\Modules\Webhooks\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class WebhookDispatcherService
{
    /**
     * Dispatch an event payload to all matching workspace webhook endpoints.
     */
    public function dispatch(string $workspaceId, string $eventType, array $payload): void
    {
        $endpoints = WebhookEndpoint::withoutGlobalScopes()
            ->where('workspace_id', $workspaceId)
            ->where('is_active', true)
            ->get();

        foreach ($endpoints as $endpoint) {
            $subscribedEvents = $endpoint->events ?? [];
            if (in_array('*', $subscribedEvents, true) || in_array($eventType, $subscribedEvents, true)) {
                $this->deliver($endpoint, $eventType, $payload);
            }
        }
    }

    /**
     * Deliver payload to a single endpoint with cryptographic HMAC-SHA256 signature.
     */
    public function deliver(WebhookEndpoint $endpoint, string $eventType, array $payload): WebhookDelivery
    {
        $timestamp = time();
        $payloadJson = json_encode([
            'event' => $eventType,
            'timestamp' => $timestamp,
            'data' => $payload,
        ], JSON_THROW_ON_ERROR);

        $signature = hash_hmac('sha256', "{$timestamp}.{$payloadJson}", $endpoint->secret);

        $delivery = WebhookDelivery::create([
            'webhook_endpoint_id' => $endpoint->id,
            'event_type' => $eventType,
            'payload' => $payload,
            'attempt' => 1,
            'status' => 'pending',
        ]);

        $start = microtime(true);

        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'SaaS-Webhook-Dispatcher/1.1',
                    'X-SaaS-Signature-256' => "t={$timestamp},v1={$signature}",
                    'X-SaaS-Event' => $eventType,
                ])
                ->withBody($payloadJson, 'application/json')
                ->post($endpoint->url);

            $duration = round((microtime(true) - $start) * 1000, 2);

            $delivery->update([
                'response_status' => $response->status(),
                'response_body' => substr($response->body(), 0, 500),
                'duration_ms' => $duration,
                'status' => $response->successful() ? 'success' : 'failed',
            ]);
        } catch (Throwable $e) {
            $duration = round((microtime(true) - $start) * 1000, 2);

            $delivery->update([
                'response_status' => 0,
                'response_body' => $e->getMessage(),
                'duration_ms' => $duration,
                'status' => 'failed',
            ]);

            Log::warning("Webhook delivery failed to {$endpoint->url}: {$e->getMessage()}");
        }

        return $delivery;
    }
}
