<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutStoreRequest;
use App\Models\CartItem;
use App\Services\CheckoutService;
use Inertia\Inertia;

class CheckoutController extends Controller
{
    public function __construct(private readonly CheckoutService $checkoutService)
    {
    }

    public function index(): \Inertia\Response
    {
        $items = CartItem::with('product')->where('user_id', auth()->id())->get();
        return Inertia::render('Checkout/Index', [
            'items' => $items,
            'defaults' => [
                'full_name' => auth()->user()?->name ?? '',
                'email' => auth()->user()?->email ?? '',
            ],
        ]);
    }

    public function store(CheckoutStoreRequest $request): \Illuminate\Http\RedirectResponse
    {
        $this->checkoutService->createOrderFromCart($request->user(), $request->validated());

        return redirect('/')
            ->with('message', 'Order placed successfully. Payment details were processed without storing card data.');
    }

}
