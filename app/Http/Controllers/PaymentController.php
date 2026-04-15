<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Payments\PaymentWebhookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentWebhookService $paymentWebhookService,
    ) {
    }

    public function show(Request $request, string $paymentReference): Response
    {
        $order = $this->findOwnedOrder($request, $paymentReference);
        abort_if($order->paymentTransaction === null, 404);

        return Inertia::render('Payments/Show', [
            'order' => $order,
            'transaction' => $order->paymentTransaction,
            'canSimulate' => $order->payment_provider === 'demo_card' && $order->payment_status === 'pending',
        ]);
    }

    public function simulate(Request $request, string $paymentReference, string $status): RedirectResponse
    {
        abort_unless(in_array($status, ['paid', 'failed'], true), 404);

        $order = $this->findOwnedOrder($request, $paymentReference);
        abort_unless($order->payment_provider === 'demo_card', 404);
        abort_if($order->paymentTransaction === null, 404);

        $payload = [
            'reference' => $order->payment_reference,
            'provider_reference' => $order->paymentTransaction?->provider_reference,
            'status' => $status,
            'source' => 'demo_payment_screen',
        ];

        $rawPayload = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = hash_hmac('sha256', $rawPayload, (string) config('payments.providers.demo_card.webhook_secret'));

        $this->paymentWebhookService->handle('demo_card', $rawPayload, $signature);

        return redirect()
            ->route('payments.show', ['paymentReference' => $paymentReference])
            ->with('message', $status === 'paid'
                ? 'Payment confirmed successfully.'
                : 'Payment failure was simulated for this demo transaction.');
    }

    private function findOwnedOrder(Request $request, string $paymentReference): Order
    {
        return Order::query()
            ->with(['items.product', 'paymentTransaction'])
            ->where('payment_reference', $paymentReference)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
    }
}
