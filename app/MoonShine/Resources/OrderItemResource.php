<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Fields\ID;
use MoonShine\Fields\Number;
use MoonShine\Fields\Relationships\BelongsTo;
use MoonShine\Resources\ModelResource;

/**
 * @extends ModelResource<OrderItem>
 */
class OrderItemResource extends ModelResource
{
    protected string $model = OrderItem::class;

    protected string $title = 'Order items';

    protected bool $withPolicy = true;

    public function fields(): array
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Orders', 'order', OrderResource::class),
            BelongsTo::make('Product', 'product', ProductResource::class),
            Number::make('Quantity', 'quantity')->min(1)->required(),
            Number::make('Price', 'price')->min(0)->step(0.01),
        ];
    }

    public function rules(Model $item): array
    {
        return [
            'order_id' => ['required', 'exists:orders,id'],
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
