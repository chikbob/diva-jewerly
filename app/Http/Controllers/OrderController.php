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
            'status' => ['nullable', 'in:pending,paid,cancelled'],
            'sort' => ['nullable', 'in:newest,oldest,total_asc,total_desc'],
        ]);

        $query = Order::query()
            ->with(['items.product'])
            ->where('user_id', Auth::id())
            ->when(
                ! empty($filters['status']),
                static fn ($builder) => $builder->where('status', $filters['status'])
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
                ['value' => 'cancelled', 'label' => 'Скасовано'],
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
