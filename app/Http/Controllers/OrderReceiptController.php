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
}
