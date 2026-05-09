<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_filters_products_by_search_category_and_price_range(): void
    {
        $rings = Category::factory()->create(['name' => 'Rings']);
        $chains = Category::factory()->create(['name' => 'Chains']);

        $matching = Product::factory()->create([
            'category_id' => $rings->id,
            'name' => 'Aurora Ring',
            'price' => 3200,
        ]);

        Product::factory()->create([
            'category_id' => $rings->id,
            'name' => 'Budget Ring',
            'price' => 900,
        ]);

        Product::factory()->create([
            'category_id' => $chains->id,
            'name' => 'Aurora Chain',
            'price' => 3200,
        ]);

        $this->get(route('catalog', [
            'search' => 'Aurora',
            'category_id' => $rings->id,
            'min_price' => 3000,
            'max_price' => 4000,
        ]))->assertInertia(fn (Assert $page) => $page
            ->component('Products/Catalog')
            ->where('filters.search', 'Aurora')
            ->where('filters.category_id', $rings->id)
            ->where('filters.min_price', 3000)
            ->where('filters.max_price', 4000)
            ->has('products.data', 1)
            ->where('products.data.0.id', $matching->id)
        );
    }

    public function test_catalog_supports_price_desc_sorting(): void
    {
        $category = Category::factory()->create();

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Lowest',
            'price' => 100,
        ]);

        $highest = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Highest',
            'price' => 9900,
        ]);

        $this->get(route('catalog', ['sort' => 'price_desc']))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Catalog')
                ->where('filters.sort', 'price_desc')
                ->where('products.data.0.id', $highest->id)
            );
    }

    public function test_catalog_can_filter_new_products_only(): void
    {
        $category = Category::factory()->create();

        $newProduct = Product::factory()->create([
            'category_id' => $category->id,
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'created_at' => now()->subDays(45),
            'updated_at' => now()->subDays(45),
        ]);

        $this->get(route('catalog', ['only_new' => 1]))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Catalog')
                ->where('filters.only_new', true)
                ->has('products.data', 1)
                ->where('products.data.0.id', $newProduct->id)
            );
    }

    public function test_product_detail_page_can_be_opened(): void
    {
        $product = Product::factory()->for(Category::factory())->create([
            'name' => 'Signature Necklace',
        ]);

        $this->get(route('products.show', $product))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Products/Show')
                ->where('product.id', $product->id)
                ->where('product.name', 'Signature Necklace')
                ->where('availability.code', 'available')
            );
    }
}
