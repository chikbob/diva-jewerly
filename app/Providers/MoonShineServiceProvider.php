<?php

declare(strict_types=1);

namespace App\Providers;

use App\MoonShine\Resources\CartItemResource;
use App\MoonShine\Resources\CategoryResource;
use App\MoonShine\Resources\OrderItemResource;
use App\MoonShine\Resources\OrderResource;
use App\MoonShine\Resources\ProductResource;
use App\MoonShine\Resources\UserResource;
use App\Support\BackofficeAccess;
use MoonShine\Models\MoonshineUser;
use MoonShine\Providers\MoonShineApplicationServiceProvider;
use MoonShine\Menu\MenuGroup;
use MoonShine\Menu\MenuItem;
use MoonShine\Contracts\Resources\ResourceContract;
use MoonShine\Menu\MenuElement;
use MoonShine\Pages\Page;
use MoonShine\Resources\MoonShineUserResource;
use MoonShine\Resources\MoonShineUserRoleResource;
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
        $access = app(BackofficeAccess::class);
        $moonshineUser = static fn (): ?MoonshineUser => auth(config('moonshine.auth.guard', 'moonshine'))->user();

        $adminUsersResource = new MoonShineUserResource();
        $adminRolesResource = new MoonShineUserRoleResource();
        $usersResource = new UserResource();
        $categoriesResource = new CategoryResource();
        $productsResource = new ProductResource();
        $cartItemsResource = new CartItemResource();
        $ordersResource = new OrderResource();
        $orderItemsResource = new OrderItemResource();

        return [
            MenuGroup::make(static fn() => __('Адмін-панель'), [
                MenuItem::make(
                    static fn() => __('Адміністратори'),
                    $adminUsersResource,
                    'heroicons.shield-check'
                )->canSee(static fn () => $access->canAccessResource($moonshineUser(), $adminUsersResource, 'viewAny')),
                MenuItem::make(
                    static fn() => __('Ролі'),
                    $adminRolesResource,
                    'heroicons.key'
                )->canSee(static fn () => $access->canAccessResource($moonshineUser(), $adminRolesResource, 'viewAny')),
            ])->canSee(static fn () => $access->hasPermission($moonshineUser(), 'admins.manage')
                || $access->hasPermission($moonshineUser(), 'roles.manage')),

            MenuGroup::make('Управління салоном', [
                MenuItem::make('Користувачі', $usersResource, 'heroicons.user-group')
                    ->canSee(static fn () => $access->canAccessResource($moonshineUser(), $usersResource, 'viewAny')),
                MenuItem::make('Категорії', $categoriesResource, 'heroicons.tag')
                    ->canSee(static fn () => $access->canAccessResource($moonshineUser(), $categoriesResource, 'viewAny')),
                MenuItem::make('Товари', $productsResource, 'heroicons.cube')
                    ->canSee(static fn () => $access->canAccessResource($moonshineUser(), $productsResource, 'viewAny')),
                MenuItem::make('Кошики', $cartItemsResource, 'heroicons.shopping-cart')
                    ->canSee(static fn () => $access->canAccessResource($moonshineUser(), $cartItemsResource, 'viewAny')),
                MenuItem::make('Замовлення', $ordersResource, 'heroicons.receipt-refund')
                    ->canSee(static fn () => $access->canAccessResource($moonshineUser(), $ordersResource, 'viewAny')),
                MenuItem::make('Товари в замовленнях', $orderItemsResource, 'heroicons.clipboard-document-check')
                    ->canSee(static fn () => $access->canAccessResource($moonshineUser(), $orderItemsResource, 'viewAny')),
            ])->canSee(static fn () => $access->hasPermission($moonshineUser(), 'catalog.view')
                || $access->hasPermission($moonshineUser(), 'catalog.manage')
                || $access->hasPermission($moonshineUser(), 'customers.view')
                || $access->hasPermission($moonshineUser(), 'customers.manage')
                || $access->hasPermission($moonshineUser(), 'operations.view')),
        ];
    }

    /**
     * @return Closure|array{css: string, colors: array, darkColors: array}
     */
    protected function theme(): array
    {
        return [];
    }

    public function boot(): void
    {
        parent::boot();

        moonshine()->defineAuthorization(
            static fn ($resource, $user, $ability, $item): bool => app(BackofficeAccess::class)
                ->canAccessResource($user, $resource, $ability)
        );
    }
}
