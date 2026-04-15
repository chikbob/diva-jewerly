<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\RemoveFromCartRequest;
use App\Http\Requests\UpdateCartQuantityRequest;
use App\Models\CartItem;
use App\Support\CartCounter;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CartController extends Controller
{
    public function index(): \Inertia\Response
    {
        $items = CartItem::query()
            ->with('product')
            ->where('user_id', auth()->id())
            ->orderByDesc('updated_at')
            ->get();

        return Inertia::render('Cart/Index', ['items' => $items]);
    }

    public function add(AddToCartRequest $request): \Illuminate\Http\RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $item = CartItem::query()
                ->where('user_id', $request->user()->id)
                ->where('product_id', $request->integer('product_id'))
                ->lockForUpdate()
                ->first();

            if ($item !== null) {
                $item->increment('quantity');

                return;
            }

            CartItem::create([
                'user_id' => $request->user()->id,
                'product_id' => $request->integer('product_id'),
                'quantity' => 1,
            ]);
        });

        return redirect()->back()->with('message', 'Добавлено в корзину!');
    }


    public function remove(RemoveFromCartRequest $request): \Illuminate\Http\RedirectResponse
    {
        CartItem::where('user_id', auth()->id())
            ->where('product_id', $request->integer('product_id'))
            ->delete();
        CartCounter::forgetForUserId($request->user()->id);

        return redirect()->back()->with('message', 'Удалено из корзины!');
    }

    public function update(UpdateCartQuantityRequest $request): \Illuminate\Http\RedirectResponse
    {
        CartItem::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $request->integer('product_id'))
            ->firstOrFail()
            ->update([
                'quantity' => $request->integer('quantity'),
            ]);

        return redirect()->back()->with('message', 'Количество товара обновлено.');
    }
}
