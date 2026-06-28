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

    protected string $title = 'Orders';

    protected bool $withPolicy = true;

    public function query(): Builder
    {
        return parent::query()->latest('created_at');
    }

    public function fields(): array
    {
        return [
            ID::make()->sortable(),
            BelongsTo::make('User', 'user', UserResource::class)->nullable(),
            Date::make('Created', 'created_at')
                ->format('d.m.Y')
                ->sortable()
                ->hideOnForm(),
            Text::make('Full name', 'full_name')->required(),
            Email::make('Email')->required(),
            Text::make('Payment method', 'payment_method')->required(),
            Text::make('Payment provider', 'payment_provider')->readonly(),
            Text::make('Payment reference', 'payment_reference')->readonly(),
            Text::make('Payment status', 'payment_status')
                ->readonly()
                ->badge(fn (string $value): string => $this->statusColor($value)),
            Text::make('Status', 'status')
                ->readonly()
                ->badge(fn (string $value): string => $this->statusColor($value)),
            Number::make('Total', 'total')->min(0)->step(0.01)->sortable(),
        ];
    }

    public function filters(): array
    {
        return [
            Select::make('Status', 'status')->options($this->statusOptions()),
            Select::make('Payment status', 'payment_status')->options($this->statusOptions()),
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
            'pending' => 'Pending',
            'paid' => 'Paid',
            'failed' => 'Failed',
            'cancelled' => 'Cancelled',
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
