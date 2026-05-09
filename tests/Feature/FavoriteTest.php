<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_add_product_to_favorites(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->for(Category::factory())->create();

        $response = $this->actingAs($user)->post(route('favorites.store', $product));

        $response->assertRedirect();
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_authenticated_user_can_remove_product_from_favorites(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->for(Category::factory())->create();

        $this->actingAs($user)->post(route('favorites.store', $product));

        $response = $this->actingAs($user)->delete(route('favorites.destroy', $product));

        $response->assertRedirect();
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
    }

    public function test_authenticated_user_can_view_favorites_page(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->for(Category::factory())->create([
            'name' => 'Saved Ring',
        ]);

        $this->actingAs($user)->post(route('favorites.store', $product));

        $this->actingAs($user)
            ->get(route('favorites.index'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('Favorites/Index')
                ->has('favorites', 1)
                ->where('favorites.0.product.name', 'Saved Ring')
            );
    }
}
