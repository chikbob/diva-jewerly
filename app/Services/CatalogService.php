<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CatalogService
{
    public function products(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        return $this->applyFilters(Product::query()->with('category'), $filters)
            ->paginate($perPage)
            ->withQueryString();
    }

    public function categories(): Collection
    {
        return Cache::remember(
            'catalog.categories',
            now()->addMinutes(10),
            static fn () => Category::query()->orderBy('name')->get()
        );
    }

    public function priceRange(): array
    {
        return Cache::remember(
            'catalog.price_range',
            now()->addMinutes(10),
            static fn (): array => [
                'min' => (float) (Product::query()->min('price') ?? 0),
                'max' => (float) (Product::query()->max('price') ?? 0),
            ]
        );
    }

    private function applyFilters(Builder $query, array $filters): Builder
    {
        if (! empty($filters['search'])) {
            $search = '%'.$filters['search'].'%';

            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('name', 'like', $search)
                    ->orWhere('description', 'like', $search);
            });
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

        if (! empty($filters['only_new'])) {
            $query->where('created_at', '>=', now()->subDays(30));
        }

        return $this->applySorting($query, $filters['sort'] ?? 'name_asc');
    }

    private function applySorting(Builder $query, string $sort): Builder
    {
        match ($sort) {
            'name_desc' => $query->orderByDesc('name'),
            'price_asc' => $query->orderBy('price')->orderBy('name'),
            'price_desc' => $query->orderByDesc('price')->orderBy('name'),
            'newest' => $query->latest(),
            default => $query->orderBy('name'),
        };

        return $query;
    }
}
