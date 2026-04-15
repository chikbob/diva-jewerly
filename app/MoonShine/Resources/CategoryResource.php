<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Fields\ID;
use MoonShine\Fields\Preview;
use MoonShine\Fields\Text;
use MoonShine\Fields\Textarea;
use MoonShine\Resources\ModelResource;

/**
 * @extends ModelResource<Category>
 */
class CategoryResource extends ModelResource
{
    protected string $model = Category::class;

    protected string $title = 'Категорії';

    protected bool $withPolicy = true;

    public function fields(): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Назва', 'name')->required(),
            Textarea::make('Опис', 'description'),
            Text::make('URL зображення', 'image_url'),
            Preview::make('Зображення', 'image_url')
                ->image()
                ->nullable(),
        ];
    }

    public function rules(Model $item): array
    {
        return [
            'name' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'url'],
        ];
    }
}
