<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Fields\ID;
use MoonShine\Fields\Preview;
use MoonShine\Fields\Text;
use MoonShine\Fields\Textarea;
use MoonShine\Fields\Number;
use MoonShine\Fields\Relationships\BelongsTo;
use MoonShine\Resources\ModelResource;

/**
 * @extends ModelResource<Product>
 */
class ProductResource extends ModelResource
{
    protected string $model = Product::class;

    protected string $title = 'Товари';

    public function fields(): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Назва', 'name')->required(),
            Textarea::make('Опис', 'description'),
            Number::make('Ціна', 'price')->min(0)->step(0.01)->required(),
            Text::make('URL зображення', 'image_path'),
            Preview::make('Зображення', 'image_path')
                ->image(),
            BelongsTo::make('Категорія', 'category', CategoryResource::class),
        ];
    }

    public function rules(Model $item): array
    {
        return [
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'image_path' => ['nullable', 'url'],
            'category_id' => ['required', 'exists:categories,id'],
        ];
    }
}
