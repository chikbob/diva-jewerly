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
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['nullable', 'in:name_asc,name_desc,price_asc,price_desc,newest'],
        ]);

        if (array_key_exists('category_id', $filters) && $filters['category_id'] !== null) {
            $filters['category_id'] = (int) $filters['category_id'];
        }

        if (array_key_exists('min_price', $filters) && $filters['min_price'] !== null) {
            $filters['min_price'] = (float) $filters['min_price'];
        }

        if (array_key_exists('max_price', $filters) && $filters['max_price'] !== null) {
            $filters['max_price'] = (float) $filters['max_price'];
        }

        $query = Product::query()->with('category');

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (array_key_exists('min_price', $filters) && $filters['min_price'] !== null) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (array_key_exists('max_price', $filters) && $filters['max_price'] !== null) {
            $query->where('price', '<=', $filters['max_price']);
        }

        $sort = $filters['sort'] ?? 'name_asc';

        match ($sort) {
            'name_desc' => $query->orderByDesc('name'),
            'price_asc' => $query->orderBy('price')->orderBy('name'),
            'price_desc' => $query->orderByDesc('price')->orderBy('name'),
            'newest' => $query->latest(),
            default => $query->orderBy('name'),
        };

        $products = $query
            ->paginate(12)
            ->withQueryString();

        $categories = Cache::remember(
            'catalog.categories',
            now()->addMinutes(10),
            static fn () => Category::query()->orderBy('name')->get()
        );

        $priceRange = Cache::remember(
            'catalog.price_range',
            now()->addMinutes(10),
            static fn (): array => [
                'min' => (float) (Product::query()->min('price') ?? 0),
                'max' => (float) (Product::query()->max('price') ?? 0),
            ]
        );

        return Inertia::render('Products/Catalog', [
            'products' => $products,
            'categories' => $categories,
            'filters' => $filters,
            'priceRange' => $priceRange,
            'sortOptions' => [
                ['value' => 'name_asc', 'label' => 'Назва: А-Я'],
                ['value' => 'name_desc', 'label' => 'Назва: Я-А'],
                ['value' => 'price_asc', 'label' => 'Ціна: від дешевих'],
                ['value' => 'price_desc', 'label' => 'Ціна: від дорогих'],
                ['value' => 'newest', 'label' => 'Новинки'],
            ],
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
