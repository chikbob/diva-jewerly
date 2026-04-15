<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request): \Inertia\Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ]);

        $query = Product::query()->with('category');

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        $products = $query
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $categories = Cache::remember(
            'catalog.categories',
            now()->addMinutes(10),
            static fn () => Category::query()->orderBy('name')->get()
        );

        return Inertia::render('Products/Catalog', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
        ]);
    }

    public function home(): \Inertia\Response
    {
        $categories = Cache::remember(
            'home.categories',
            now()->addMinutes(10),
            static fn () => Category::query()->orderBy('name')->get()
        );

        return Inertia::render('Products/Home', [
            'categories' => $categories
        ]);
    }

}
