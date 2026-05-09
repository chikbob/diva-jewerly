<?php

namespace App\Http\Controllers;

use App\Http\Requests\CatalogIndexRequest;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Product;
use App\Services\CatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function __construct(private readonly CatalogService $catalogService) {}

    public function index(CatalogIndexRequest $request): \Inertia\Response
    {
        $filters = $request->filters();
        $products = $this->catalogService->products($filters);
        $favoriteIds = $request->user() === null
            ? []
            : Favorite::query()
                ->where('user_id', $request->user()->id)
                ->whereIn('product_id', collect($products->items())->pluck('id'))
                ->pluck('product_id')
                ->map(static fn (int $id): int => $id)
                ->all();

        return Inertia::render('Products/Catalog', [
            'products' => $products,
            'categories' => $this->catalogService->categories(),
            'filters' => $filters,
            'favoriteIds' => $favoriteIds,
            'priceRange' => $this->catalogService->priceRange(),
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
            'categories' => $categories,
        ]);
    }

    public function show(Request $request, Product $product): \Inertia\Response
    {
        $product->load('category');

        return Inertia::render('Products/Show', [
            'product' => $product,
            'availability' => [
                'code' => 'available',
                'label' => 'Доступно до замовлення',
            ],
            'isFavorited' => $request->user() !== null
                && Favorite::query()
                    ->where('user_id', $request->user()->id)
                    ->where('product_id', $product->id)
                    ->exists(),
        ]);
    }
}
