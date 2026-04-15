<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $filters = $request->validate([
            'status' => ['nullable', 'in:pending,paid,failed,cancelled'],
            'payment_status' => ['nullable', 'in:pending,paid,failed,cancelled'],
            'sort' => ['nullable', 'in:newest,oldest,total_asc,total_desc'],
        ]);

        $query = Order::query()
            ->with(['items.product', 'paymentTransaction'])
            ->where('user_id', Auth::id())
            ->when(
                ! empty($filters['status']),
                static fn ($builder) => $builder->where('status', $filters['status'])
            )
            ->when(
                ! empty($filters['payment_status']),
                static fn ($builder) => $builder->where('payment_status', $filters['payment_status'])
            );

        match ($filters['sort'] ?? 'newest') {
            'oldest' => $query->oldest(),
            'total_asc' => $query->orderBy('total')->orderByDesc('created_at'),
            'total_desc' => $query->orderByDesc('total')->orderByDesc('created_at'),
            default => $query->latest(),
        };

        $orders = $query->get();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'filters' => $filters,
            'statusOptions' => [
                ['value' => '', 'label' => 'Усі статуси'],
                ['value' => 'pending', 'label' => 'В очікуванні'],
                ['value' => 'paid', 'label' => 'Сплачено'],
                ['value' => 'failed', 'label' => 'Помилка оплати'],
                ['value' => 'cancelled', 'label' => 'Скасовано'],
            ],
            'paymentStatusOptions' => [
                ['value' => '', 'label' => 'Будь-який payment status'],
                ['value' => 'pending', 'label' => 'Payment pending'],
                ['value' => 'paid', 'label' => 'Payment paid'],
                ['value' => 'failed', 'label' => 'Payment failed'],
                ['value' => 'cancelled', 'label' => 'Payment cancelled'],
            ],
            'sortOptions' => [
                ['value' => 'newest', 'label' => 'Спочатку нові'],
                ['value' => 'oldest', 'label' => 'Спочатку старі'],
                ['value' => 'total_desc', 'label' => 'Сума: за спаданням'],
                ['value' => 'total_asc', 'label' => 'Сума: за зростанням'],
            ],
        ]);
    }
}
