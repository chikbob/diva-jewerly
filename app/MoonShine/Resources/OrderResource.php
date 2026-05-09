<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Fields\Date;
use MoonShine\Fields\Email;
use MoonShine\Fields\ID;
use MoonShine\Fields\Number;
use MoonShine\Fields\Relationships\BelongsTo;
use MoonShine\Fields\Select;
use MoonShine\Fields\Text;
use MoonShine\Resources\ModelResource;

/**
 * @extends ModelResource<Order>
 */
class OrderResource extends ModelResource
{
    protected string $model = Order::class;

    protected string $title = 'Замовлення';

    protected bool $withPolicy = true;

    public function query(): Builder
    {
        return parent::query()->latest('created_at');
    }

    public function fields(): array
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Користувач', 'user', UserResource::class)->nullable(),
            Date::make('Створено', 'created_at')
                ->format('d.m.Y')
                ->sortable()
                ->hideOnForm(),
            Text::make('ПІБ', 'full_name')->required(),
            Email::make('Email')->required(),
            Text::make('Спосіб оплати', 'payment_method')->required(),
            Text::make('Платіжний провайдер', 'payment_provider')->readonly(),
            Text::make('Платіжне посилання', 'payment_reference')->readonly(),
            Text::make('Статус оплати', 'payment_status')
                ->readonly()
                ->badge(fn (string $value): string => $this->statusColor($value)),
            Text::make('Статус', 'status')
                ->readonly()
                ->badge(fn (string $value): string => $this->statusColor($value)),
            Number::make('Сума', 'total')->min(0)->step(0.01)->sortable(),
        ];
    }

    public function filters(): array
    {
        return [
            Select::make('Статус', 'status')->options($this->statusOptions()),
            Select::make('Статус оплати', 'payment_status')->options($this->statusOptions()),
        ];
    }

    public function search(): array
    {
        return ['id', 'full_name', 'email', 'payment_reference'];
    }

    public function rules(Model $item): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'full_name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'payment_method' => ['required', 'string'],
            'payment_provider' => ['nullable', 'string'],
            'payment_reference' => ['nullable', 'string'],
            'payment_status' => ['nullable', 'string'],
            'status' => ['required', 'string'],
            'total' => ['required', 'numeric', 'min:0'],
        ];
    }

    private function statusOptions(): array
    {
        return [
            'pending' => 'В очікуванні',
            'paid' => 'Сплачено',
            'failed' => 'Помилка',
            'cancelled' => 'Скасовано',
        ];
    }

    private function statusColor(string $value): string
    {
        return match ($value) {
            'paid' => 'green',
            'failed' => 'red',
            'cancelled' => 'gray',
            default => 'yellow',
        };
    }
}
