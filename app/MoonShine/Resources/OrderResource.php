<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Order;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Fields\ID;
use MoonShine\Fields\Text;
use MoonShine\Fields\Email;
use MoonShine\Fields\Number;
use MoonShine\Fields\Relationships\BelongsTo;
use MoonShine\Resources\ModelResource;

/**
 * @extends ModelResource<Order>
 */
class OrderResource extends ModelResource
{
    protected string $model = Order::class;

    protected string $title = 'Замовлення';

    protected bool $withPolicy = true;

    public function fields(): array
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Користувач', 'user', UserResource::class)->nullable(),
            Text::make('ПІБ', 'full_name')->required(),
            Email::make('Email')->required(),
            Text::make('Спосіб оплати', 'payment_method')->required(),
            Text::make('Платіжне посилання', 'payment_reference')->readonly(),
            Text::make('Статус', 'status'),
            Number::make('Сума', 'total')->min(0)->step(0.01),
        ];
    }

    public function rules(Model $item): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'full_name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'payment_method' => ['required', 'string'],
            'payment_reference' => ['nullable', 'string'],
            'status' => ['required', 'string'],
            'total' => ['required', 'numeric', 'min:0'],
        ];
    }
}
