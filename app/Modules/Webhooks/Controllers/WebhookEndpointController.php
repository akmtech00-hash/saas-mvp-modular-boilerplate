<?php

declare(strict_types=1);

namespace App\Modules\Webhooks\Controllers;

use App\Modules\Webhooks\Models\WebhookEndpoint;
use App\Modules\Webhooks\Services\WebhookDispatcherService;
use App\Modules\Workspace\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class WebhookEndpointController extends Controller
{
    public function __construct(
        private readonly WebhookDispatcherService $dispatcherService
    ) {}

    /**
     * List all registered outbound webhook endpoints for current workspace.
     */
    public function index(): JsonResponse
    {
        $endpoints = WebhookEndpoint::latest()->get();

        return response()->json([
            'data' => $endpoints,
        ]);
    }

    /**
     * Register a new webhook endpoint.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'url' => 'required|url|max:255',
            'events' => 'required|array|min:1',
            'events.*' => 'string|max:50',
        ]);

        $workspaceId = app(TenantContext::class)->getWorkspaceId();

        $secret = 'whsec_' . Str::random(32);

        $endpoint = WebhookEndpoint::create([
            'workspace_id' => $workspaceId,
            'url' => $validated['url'],
            'secret' => $secret,
            'events' => $validated['events'],
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'Webhook endpoint registered successfully.',
            'endpoint' => $endpoint,
            // Reveal secret once on creation
            'signing_secret' => $secret,
        ], 201);
    }

    /**
     * List delivery history for an endpoint.
     */
    public function deliveries(int $id): JsonResponse
    {
        $endpoint = WebhookEndpoint::findOrFail($id);

        $deliveries = $endpoint->deliveries()->latest()->take(50)->get();

        return response()->json([
            'endpoint_id' => $id,
            'deliveries' => $deliveries,
        ]);
    }

    /**
     * Trigger a test ping event to the endpoint.
     */
    public function testPing(int $id): JsonResponse
    {
        $endpoint = WebhookEndpoint::findOrFail($id);

        $delivery = $this->dispatcherService->deliver($endpoint, 'ping.test', [
            'message' => 'This is a test notification from SaaS MVP Boilerplate.',
            'workspace_id' => $endpoint->workspace_id,
        ]);

        return response()->json([
            'message' => 'Test ping dispatched.',
            'delivery' => $delivery,
        ]);
    }

    /**
     * Delete an endpoint.
     */
    public function destroy(int $id): JsonResponse
    {
        $endpoint = WebhookEndpoint::findOrFail($id);
        $endpoint->delete();

        return response()->json([
            'message' => 'Webhook endpoint deleted successfully.',
        ]);
    }
}
