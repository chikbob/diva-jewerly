<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderReceiptController extends Controller
{
    public function show(Request $request, Order $order): View
    {
        $order = $this->resolveOrder($request, $order);

        return view('orders.receipt', [
            'order' => $order,
            'receiptPayload' => $this->receiptPayload($order),
            'autoPrint' => $request->boolean('print'),
            'autoDownloadPdf' => false,
            'showActions' => true,
        ]);
    }

    public function download(Request $request, Order $order): View
    {
        $order = $this->resolveOrder($request, $order);

        return view('orders.receipt', [
            'order' => $order,
            'receiptPayload' => $this->receiptPayload($order),
            'autoPrint' => false,
            'autoDownloadPdf' => true,
            'showActions' => false,
        ]);
    }

    private function resolveOrder(Request $request, Order $order): Order
    {
        return Order::query()
            ->with(['items.product.category'])
            ->whereKey($order->id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();
    }

    private function receiptPayload(Order $order): array
    {
        return [
            'id' => $order->id,
            'created_at' => $order->created_at?->toIso8601String(),
            'full_name' => $order->full_name,
            'email' => $order->email,
            'payment_method' => $order->payment_method,
            'payment_status' => $order->payment_status,
            'payment_reference' => $order->payment_reference,
            'status' => $order->status,
            'total' => (float) $order->total,
            'items' => $order->items->map(fn ($item): array => [
                'product_name' => $item->product?->name ?? 'Product unavailable',
                'product_category' => $item->product?->category?->name,
                'product_description' => $item->product?->description,
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
                'line_total' => (float) $item->price * (int) $item->quantity,
            ])->values()->all(),
        ];
    }
}
