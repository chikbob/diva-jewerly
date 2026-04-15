<?php

declare(strict_types=1);

namespace App\Providers;

use App\MoonShine\Resources\CartItemResource;
use App\MoonShine\Resources\CategoryResource;
use App\MoonShine\Resources\OrderItemResource;
use App\MoonShine\Resources\OrderResource;
use App\MoonShine\Resources\ProductResource;
use App\MoonShine\Resources\UserResource;
use MoonShine\Providers\MoonShineApplicationServiceProvider;
use MoonShine\MoonShine;
use MoonShine\Menu\MenuGroup;
use MoonShine\Menu\MenuItem;
use MoonShine\Resources\MoonShineUserResource;
use MoonShine\Resources\MoonShineUserRoleResource;
use MoonShine\Contracts\Resources\ResourceContract;
use MoonShine\Menu\MenuElement;
use MoonShine\Pages\Page;
use Closure;

class MoonShineServiceProvider extends MoonShineApplicationServiceProvider
{
    /**
     * @return list<ResourceContract>
     */
    protected function resources(): array
    {
        return [];
    }

    /**
     * @return list<Page>
     */
    protected function pages(): array
    {
        return [];
    }

    /**
     * @return Closure|list<MenuElement>
     */
    protected function menu(): array
    {
        return [
            MenuGroup::make(static fn() => __('Адмін-панель'), [
                MenuItem::make(
                    static fn() => __('Адміністратори'),
                    new \MoonShine\Resources\MoonShineUserResource(),
                    'heroicons.shield-check'
                ),
                MenuItem::make(
                    static fn() => __('Ролі'),
                    new \MoonShine\Resources\MoonShineUserRoleResource(),
                    'heroicons.key'
                ),
            ]),

            MenuGroup::make('Управління салоном', [
                MenuItem::make('Користувачі', new UserResource(), 'heroicons.user-group'),
                MenuItem::make('Категорії', new CategoryResource(), 'heroicons.tag'),
                MenuItem::make('Товари', new ProductResource(), 'heroicons.cube'),
                MenuItem::make('Кошики', new CartItemResource(), 'heroicons.shopping-cart'),
                MenuItem::make('Замовлення', new OrderResource(), 'heroicons.receipt-refund'),
                MenuItem::make('Товари в замовленнях', new OrderItemResource(), 'heroicons.clipboard-document-check'),
            ])
        ];
    }

    /**
     * @return Closure|array{css: string, colors: array, darkColors: array}
     */
    protected function theme(): array
    {
        return [];
    }
}
