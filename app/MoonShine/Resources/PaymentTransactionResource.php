<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\PaymentTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Fields\Date;
use MoonShine\Fields\ID;
use MoonShine\Fields\Number;
use MoonShine\Fields\Relationships\BelongsTo;
use MoonShine\Fields\Select;
use MoonShine\Fields\Text;
use MoonShine\Resources\ModelResource;

/**
 * @extends ModelResource<PaymentTransaction>
 */
class PaymentTransactionResource extends ModelResource
{
    protected string $model = PaymentTransaction::class;

    protected string $title = 'Платежі';

    protected bool $withPolicy = true;

    public function query(): Builder
    {
        return parent::query()->latest('created_at');
    }

    public function fields(): array
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('Замовлення', 'order', OrderResource::class),
            Date::make('Створено', 'created_at')
                ->format('d.m.Y')
                ->sortable()
                ->hideOnForm(),
            Text::make('Провайдер', 'provider')->badge('purple'),
            Text::make('Метод', 'payment_method'),
            Text::make('Reference', 'reference')->readonly(),
            Text::make('Provider reference', 'provider_reference')->readonly(),
            Text::make('Статус', 'status')
                ->readonly()
                ->badge(fn (string $value): string => $this->statusColor($value)),
            Number::make('Сума', 'amount')->min(0)->step(0.01)->sortable(),
            Text::make('Валюта', 'currency')->readonly(),
        ];
    }

    public function filters(): array
    {
        return [
            Select::make('Провайдер', 'provider')->options($this->providerOptions()),
            Select::make('Статус', 'status')->options($this->statusOptions()),
        ];
    }

    public function search(): array
    {
        return ['id', 'reference', 'provider_reference', 'provider', 'payment_method'];
    }

    public function rules(Model $item): array
    {
        return [
            'order_id' => ['required', 'exists:orders,id'],
            'provider' => ['required', 'string'],
            'payment_method' => ['required', 'string'],
            'reference' => ['required', 'string'],
            'provider_reference' => ['nullable', 'string'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['required', 'string'],
            'status' => ['required', 'string'],
        ];
    }

    private function providerOptions(): array
    {
        return [
            'demo_card' => 'Demo card',
            'cash_on_delivery' => 'Cash on delivery',
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
