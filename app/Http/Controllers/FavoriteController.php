<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FavoriteController extends Controller
{
    public function index(Request $request): Response
    {
        $favorites = Favorite::query()
            ->with('product.category')
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return Inertia::render('Favorites/Index', [
            'favorites' => $favorites,
        ]);
    }

    public function store(Request $request, Product $product): RedirectResponse
    {
        Favorite::query()->firstOrCreate([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
        ]);

        return redirect()->back()->with('message', 'Item added to favorites.');
    }

    public function destroy(Request $request, Product $product): RedirectResponse
    {
        Favorite::query()
            ->where('user_id', $request->user()->id)
            ->where('product_id', $product->id)
            ->delete();

        return redirect()->back()->with('message', 'Item removed from favorites.');
    }
}
