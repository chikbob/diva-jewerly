<?php

namespace App\Http\Controllers;

use App\Services\Payments\PaymentWebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentWebhookService $paymentWebhookService,
    ) {
    }

    public function __invoke(Request $request, string $provider): JsonResponse
    {
        $transaction = $this->paymentWebhookService->handle(
            $provider,
            $request->getContent(),
            $request->header('X-Diva-Signature')
        );

        return response()->json([
            'ok' => true,
            'reference' => $transaction->reference,
            'status' => $transaction->status,
        ]);
    }
}
