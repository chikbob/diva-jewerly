<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Fields\ID;
use MoonShine\Fields\Number;
use MoonShine\Fields\Preview;
use MoonShine\Fields\Relationships\BelongsTo;
use MoonShine\Fields\Text;
use MoonShine\Fields\Textarea;
use MoonShine\Resources\ModelResource;

/**
 * @extends ModelResource<Product>
 */
class ProductResource extends ModelResource
{
    protected string $model = Product::class;

    protected string $title = 'Products';

    protected bool $withPolicy = true;

    public function fields(): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Name', 'name')->required(),
            Textarea::make('Description', 'description'),
            Number::make('Price', 'price')->min(0)->step(0.01)->required()->sortable(),
            Text::make('Image URL', 'image_path'),
            Preview::make('Image', 'image_path')
                ->image(),
            BelongsTo::make('Category', 'category', CategoryResource::class),
        ];
    }

    public function search(): array
    {
        return ['id', 'name', 'description'];
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
