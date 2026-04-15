<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use MoonShine\Fields\Email;
use MoonShine\Fields\ID;
use MoonShine\Fields\Password;
use MoonShine\Fields\Text;
use MoonShine\Resources\ModelResource;

/**
 * @extends ModelResource<User>
 */
class UserResource extends ModelResource
{
    protected string $model = User::class;

    protected string $title = 'Користувачі';

    public function fields(): array
    {
        return [
            ID::make()->sortable(),
            Text::make('Ім\'я', 'name')->required(),
            Email::make('Email')->required(),
            Password::make('Пароль', 'password')->hideOnIndex(),
        ];
    }

    public function rules(Model $item): array
    {
        return [
            'name' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['nullable', 'string', 'min:6'],
        ];
    }
}
