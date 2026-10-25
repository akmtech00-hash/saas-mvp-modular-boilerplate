<?php

declare(strict_types=1);

namespace App\Modules\Billing\Controllers;

use App\Modules\Billing\Services\StripeBillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Stripe\Exception\SignatureVerificationException;
use Throwable;

class BillingWebhookController extends Controller
{
    public function __construct(
        private readonly StripeBillingService $billingService
    ) {}

    /**
     * Handle incoming Stripe webhook notifications.
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $result = $this->billingService->verifyAndHandleWebhook($payload, $sigHeader);

            return response()->json($result, 200);
        } catch (SignatureVerificationException $e) {
            return response()->json([
                'error' => 'invalid_signature',
                'message' => 'Stripe webhook signature verification failed.',
            ], 400);
        } catch (Throwable $e) {
            return response()->json([
                'error' => 'webhook_processing_error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
