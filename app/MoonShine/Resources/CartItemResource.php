<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\CartItem;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Fields\ID;
use MoonShine\Fields\Number;
use MoonShine\Fields\Relationships\BelongsTo;
use MoonShine\Resources\ModelResource;

/**
 * @extends ModelResource<CartItem>
 */
class CartItemResource extends ModelResource
{
    protected string $model = CartItem::class;

    protected string $title = 'Кошик';

    protected bool $withPolicy = true;

    public function fields(): array
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Користувач', 'user', UserResource::class)->nullable(),
            BelongsTo::make('Товар', 'product', ProductResource::class),
            Number::make('Кількість', 'quantity')->min(1)->required(),
        ];
    }

    public function rules(Model $item): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
