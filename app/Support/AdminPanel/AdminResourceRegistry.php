<?php

namespace App\Support\AdminPanel;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use MoonShine\Models\MoonshineUser;
use MoonShine\Models\MoonshineUserRole;

class AdminResourceRegistry
{
    public function navigation(): array
    {
        $groups = [];

        foreach ($this->definitions() as $key => $resource) {
            $groups[$resource['group']][] = [
                'key' => $key,
                'label' => $resource['label'],
                'description' => $resource['description'],
                'icon' => $resource['icon'],
                'domain' => $resource['domain'],
            ];
        }

        return collect($groups)->map(
            fn (array $items, string $label): array => [
                'label' => $label,
                'items' => $items,
            ]
        )->values()->all();
    }

    public function get(string $key): array
    {
        $resource = $this->definitions()[$key] ?? null;

        abort_if($resource === null, 404);

        return [
            'key' => $key,
            ...$resource,
        ];
    }

    public function modelClass(array $resource): string
    {
        return $resource['model'];
    }

    public function queryForIndex(array $resource, array $params = []): Builder
    {
        /** @var class-string<Model> $modelClass */
        $modelClass = $resource['model'];

        $query = $modelClass::query()->with($resource['with'] ?? []);
        $search = trim((string) ($params['search'] ?? ''));

        if ($search !== '' && ! empty($resource['search'])) {
            $query->where(function (Builder $builder) use ($resource, $search): void {
                foreach ($resource['search'] as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $builder->{$method}($column, 'like', "%{$search}%");
                }
            });
        }

        foreach ($resource['filters'] ?? [] as $filter) {
            $value = $params[$filter['name']] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            $query->where($filter['column'], $value);
        }

        $sortKey = (string) ($params['sort'] ?? '');
        $sortDirection = strtolower((string) ($params['direction'] ?? 'desc'));
        $sorts = collect($resource['sorts'] ?? [])->keyBy('name');
        $selectedSort = $sorts->get($sortKey);

        if ($selectedSort !== null) {
            $query->orderBy($selectedSort['column'], $sortDirection === 'asc' ? 'asc' : 'desc');

            return $query;
        }

        [$column, $direction] = $resource['default_sort'];
        $query->orderBy($column, $direction);

        return $query;
    }

    public function indexSchema(array $resource): array
    {
        return [
            'filters' => collect($resource['filters'] ?? [])->map(fn (array $filter): array => [
                'name' => $filter['name'],
                'label' => $filter['label'],
                'type' => $filter['type'],
                'options' => isset($filter['options']) ? $this->resolveOptions($filter['options']) : [],
            ])->values()->all(),
            'sorts' => array_values($resource['sorts'] ?? []),
        ];
    }

    public function fields(array $resource, ?Model $record = null): array
    {
        return collect($resource['fields'])->map(function (array $field) use ($record): array {
            $value = $record === null
                ? ($field['default'] ?? '')
                : $this->fieldValue($record, $field);

            return [
                'name' => $field['name'],
                'label' => $field['label'],
                'type' => $field['type'],
                'placeholder' => $field['placeholder'] ?? null,
                'help' => $field['help'] ?? null,
                'value' => $value,
                'options' => isset($field['options'])
                    ? $this->resolveOptions($field['options'])
                    : [],
            ];
        })->values()->all();
    }

    public function validateAndTransform(Request $request, array $resource, ?Model $record = null): array
    {
        $validation = $resource['validation']($record, $request);
        $validated = Validator::make($request->all(), $validation)->validate();

        foreach ($resource['fields'] as $field) {
            if (($field['type'] ?? null) === 'password' && ($validated[$field['name']] ?? '') === '') {
                unset($validated[$field['name']]);
            }

            if (($field['type'] ?? null) === 'datetime-local' && ($validated[$field['name']] ?? '') === '') {
                $validated[$field['name']] = null;
            }
        }

        if ($resource['key'] === 'staff-users' && array_key_exists('password', $validated)) {
            $validated['password'] = Hash::make($validated['password']);
        }

        if ($resource['key'] === 'payment-transactions' && isset($validated['provider_payload']) && $validated['provider_payload'] !== null && $validated['provider_payload'] !== '') {
            $decoded = json_decode($validated['provider_payload'], true);
            $validated['provider_payload'] = is_array($decoded) ? $decoded : null;
        }

        return $validated;
    }

    public function formatRecord(Model $record, array $resource): array
    {
        return [
            'id' => $record->getKey(),
            'title' => $this->recordTitle($record, $resource),
            'cells' => collect($resource['columns'])->map(
                fn (array $column): array => [
                    'key' => $column['key'],
                    'value' => $this->formatValue(data_get($record, $column['key']), $column['type'] ?? 'text'),
                ]
            )->values()->all(),
        ];
    }

    public function recordTitle(Model $record, array $resource): string
    {
        $key = $resource['title_key'] ?? $resource['columns'][0]['key'];
        $value = data_get($record, $key);

        return (string) ($value ?? ('#'.$record->getKey()));
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function definitions(): array
    {
        return [
            'staff-users' => [
                'label' => 'Співробітники',
                'description' => 'Облікові записи та доступ персоналу',
                'group' => 'Команда',
                'icon' => 'shield',
                'domain' => 'admins',
                'model' => MoonshineUser::class,
                'with' => ['moonshineUserRole'],
                'search' => ['name', 'email'],
                'default_sort' => ['created_at', 'desc'],
                'sorts' => [
                    ['name' => 'created_at', 'label' => 'За датою створення', 'column' => 'created_at'],
                    ['name' => 'name', 'label' => 'За ім’ям', 'column' => 'name'],
                    ['name' => 'email', 'label' => 'За email', 'column' => 'email'],
                ],
                'filters' => [
                    ['name' => 'moonshine_user_role_id', 'label' => 'Роль', 'type' => 'select', 'column' => 'moonshine_user_role_id', 'options' => 'staff_roles'],
                ],
                'title_key' => 'name',
                'columns' => [
                    ['key' => 'name', 'label' => "Ім'я"],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'moonshineUserRole.name', 'label' => 'Роль'],
                    ['key' => 'created_at', 'label' => 'Створено', 'type' => 'datetime'],
                ],
                'fields' => [
                    ['name' => 'name', 'label' => "Ім'я", 'type' => 'text', 'placeholder' => "Введіть ім'я"],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'placeholder' => 'name@example.com'],
                    ['name' => 'moonshine_user_role_id', 'label' => 'Роль', 'type' => 'select', 'options' => 'staff_roles'],
                    ['name' => 'avatar', 'label' => 'Аватар URL', 'type' => 'url', 'placeholder' => 'https://...'],
                    ['name' => 'password', 'label' => 'Пароль', 'type' => 'password', 'help' => 'Залиште порожнім під час редагування, якщо пароль змінювати не потрібно.'],
                ],
                'validation' => static fn (?Model $record, Request $request): array => [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'max:255', Rule::unique('moonshine_users', 'email')->ignore($record?->getKey())],
                    'moonshine_user_role_id' => ['required', 'exists:moonshine_user_roles,id'],
                    'avatar' => ['nullable', 'url', 'max:2048'],
                    'password' => [$record === null ? 'required' : 'nullable', 'string', 'min:8'],
                ],
            ],
            'staff-roles' => [
                'label' => 'Ролі доступу',
                'description' => 'Ролі для бек-офісу та персоналу',
                'group' => 'Команда',
                'icon' => 'key',
                'domain' => 'roles',
                'model' => MoonshineUserRole::class,
                'with' => [],
                'search' => ['name'],
                'default_sort' => ['name', 'asc'],
                'sorts' => [
                    ['name' => 'name', 'label' => 'За назвою', 'column' => 'name'],
                    ['name' => 'created_at', 'label' => 'За датою створення', 'column' => 'created_at'],
                ],
                'filters' => [],
                'title_key' => 'name',
                'columns' => [
                    ['key' => 'name', 'label' => 'Назва ролі'],
                    ['key' => 'created_at', 'label' => 'Створено', 'type' => 'datetime'],
                ],
                'fields' => [
                    ['name' => 'name', 'label' => 'Назва ролі', 'type' => 'text', 'placeholder' => 'Наприклад, Support'],
                ],
                'validation' => static fn (?Model $record): array => [
                    'name' => ['required', 'string', 'max:255', Rule::unique('moonshine_user_roles', 'name')->ignore($record?->getKey())],
                ],
            ],
            'categories' => [
                'label' => 'Категорії',
                'description' => 'Категорії ювелірних колекцій',
                'group' => 'Каталог',
                'icon' => 'tag',
                'domain' => 'catalog',
                'model' => Category::class,
                'with' => [],
                'search' => ['name', 'description'],
                'default_sort' => ['name', 'asc'],
                'sorts' => [
                    ['name' => 'name', 'label' => 'За назвою', 'column' => 'name'],
                    ['name' => 'created_at', 'label' => 'За датою створення', 'column' => 'created_at'],
                ],
                'filters' => [],
                'title_key' => 'name',
                'columns' => [
                    ['key' => 'name', 'label' => 'Назва'],
                    ['key' => 'description', 'label' => 'Опис'],
                    ['key' => 'created_at', 'label' => 'Створено', 'type' => 'datetime'],
                ],
                'fields' => [
                    ['name' => 'name', 'label' => 'Назва', 'type' => 'text', 'placeholder' => 'Назва категорії'],
                    ['name' => 'description', 'label' => 'Опис', 'type' => 'textarea', 'placeholder' => 'Короткий опис категорії'],
                    ['name' => 'image_url', 'label' => 'Зображення URL', 'type' => 'url', 'placeholder' => 'https://...'],
                ],
                'validation' => static fn (?Model $record): array => [
                    'name' => ['required', 'string', 'max:255', Rule::unique('categories', 'name')->ignore($record?->getKey())],
                    'description' => ['nullable', 'string'],
                    'image_url' => ['nullable', 'url', 'max:2048'],
                ],
            ],
            'products' => [
                'label' => 'Товари',
                'description' => 'Каталог прикрас, ціни та категорії',
                'group' => 'Каталог',
                'icon' => 'sparkles',
                'domain' => 'catalog',
                'model' => Product::class,
                'with' => ['category'],
                'search' => ['name', 'description'],
                'default_sort' => ['created_at', 'desc'],
                'sorts' => [
                    ['name' => 'created_at', 'label' => 'Спочатку нові', 'column' => 'created_at'],
                    ['name' => 'name', 'label' => 'За назвою', 'column' => 'name'],
                    ['name' => 'price', 'label' => 'За ціною', 'column' => 'price'],
                ],
                'filters' => [
                    ['name' => 'category_id', 'label' => 'Категорія', 'type' => 'select', 'column' => 'category_id', 'options' => 'categories'],
                ],
                'title_key' => 'name',
                'columns' => [
                    ['key' => 'name', 'label' => 'Назва'],
                    ['key' => 'category.name', 'label' => 'Категорія'],
                    ['key' => 'price', 'label' => 'Ціна', 'type' => 'money'],
                    ['key' => 'created_at', 'label' => 'Створено', 'type' => 'datetime'],
                ],
                'fields' => [
                    ['name' => 'name', 'label' => 'Назва', 'type' => 'text', 'placeholder' => 'Назва товару'],
                    ['name' => 'category_id', 'label' => 'Категорія', 'type' => 'select', 'options' => 'categories'],
                    ['name' => 'price', 'label' => 'Ціна', 'type' => 'number', 'placeholder' => '0.00'],
                    ['name' => 'image_path', 'label' => 'Зображення URL', 'type' => 'url', 'placeholder' => 'https://...'],
                    ['name' => 'description', 'label' => 'Опис', 'type' => 'textarea', 'placeholder' => 'Опис товару'],
                ],
                'validation' => static fn (): array => [
                    'name' => ['required', 'string', 'max:255'],
                    'category_id' => ['required', 'exists:categories,id'],
                    'price' => ['required', 'numeric', 'min:0'],
                    'image_path' => ['required', 'url', 'max:2048'],
                    'description' => ['nullable', 'string'],
                ],
            ],
            'users' => [
                'label' => 'Клієнти',
                'description' => 'Облікові записи покупців магазину',
                'group' => 'Клієнти',
                'icon' => 'users',
                'domain' => 'customers',
                'model' => User::class,
                'with' => [],
                'search' => ['name', 'email'],
                'default_sort' => ['created_at', 'desc'],
                'sorts' => [
                    ['name' => 'created_at', 'label' => 'Спочатку нові', 'column' => 'created_at'],
                    ['name' => 'name', 'label' => 'За ім’ям', 'column' => 'name'],
                    ['name' => 'email', 'label' => 'За email', 'column' => 'email'],
                ],
                'filters' => [],
                'title_key' => 'name',
                'columns' => [
                    ['key' => 'name', 'label' => "Ім'я"],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'email_verified_at', 'label' => 'Підтверджено', 'type' => 'datetime'],
                    ['key' => 'created_at', 'label' => 'Створено', 'type' => 'datetime'],
                ],
                'fields' => [
                    ['name' => 'name', 'label' => "Ім'я", 'type' => 'text', 'placeholder' => "Ім'я клієнта"],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'placeholder' => 'name@example.com'],
                    ['name' => 'password', 'label' => 'Пароль', 'type' => 'password', 'help' => 'Під час редагування залиште порожнім, якщо пароль не змінюється.'],
                ],
                'validation' => static fn (?Model $record): array => [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($record?->getKey())],
                    'password' => [$record === null ? 'required' : 'nullable', 'string', 'min:8'],
                ],
            ],
            'favorites' => [
                'label' => 'Обране',
                'description' => 'Збережені товари користувачів',
                'group' => 'Клієнти',
                'icon' => 'heart',
                'domain' => 'customers',
                'model' => Favorite::class,
                'with' => ['user', 'product'],
                'search' => [],
                'default_sort' => ['created_at', 'desc'],
                'sorts' => [
                    ['name' => 'created_at', 'label' => 'Спочатку нові', 'column' => 'created_at'],
                ],
                'filters' => [
                    ['name' => 'user_id', 'label' => 'Користувач', 'type' => 'select', 'column' => 'user_id', 'options' => 'users'],
                    ['name' => 'product_id', 'label' => 'Товар', 'type' => 'select', 'column' => 'product_id', 'options' => 'products'],
                ],
                'title_key' => 'id',
                'columns' => [
                    ['key' => 'user.name', 'label' => 'Користувач'],
                    ['key' => 'product.name', 'label' => 'Товар'],
                    ['key' => 'created_at', 'label' => 'Додано', 'type' => 'datetime'],
                ],
                'fields' => [
                    ['name' => 'user_id', 'label' => 'Користувач', 'type' => 'select', 'options' => 'users'],
                    ['name' => 'product_id', 'label' => 'Товар', 'type' => 'select', 'options' => 'products'],
                ],
                'validation' => static fn (?Model $record, Request $request): array => [
                    'user_id' => ['required', 'exists:users,id'],
                    'product_id' => [
                        'required',
                        'exists:products,id',
                        Rule::unique('favorites', 'product_id')
                            ->where(static fn ($query) => $query->where('user_id', $request->integer('user_id')))
                            ->ignore($record?->getKey()),
                    ],
                ],
            ],
            'cart-items' => [
                'label' => 'Кошики',
                'description' => 'Поточні товари у кошиках клієнтів',
                'group' => 'Клієнти',
                'icon' => 'shopping-cart',
                'domain' => 'operations',
                'model' => CartItem::class,
                'with' => ['user', 'product'],
                'search' => [],
                'default_sort' => ['created_at', 'desc'],
                'sorts' => [
                    ['name' => 'created_at', 'label' => 'Спочатку нові', 'column' => 'created_at'],
                    ['name' => 'quantity', 'label' => 'За кількістю', 'column' => 'quantity'],
                ],
                'filters' => [
                    ['name' => 'user_id', 'label' => 'Користувач', 'type' => 'select', 'column' => 'user_id', 'options' => 'users'],
                    ['name' => 'product_id', 'label' => 'Товар', 'type' => 'select', 'column' => 'product_id', 'options' => 'products'],
                ],
                'title_key' => 'id',
                'columns' => [
                    ['key' => 'user.name', 'label' => 'Користувач'],
                    ['key' => 'product.name', 'label' => 'Товар'],
                    ['key' => 'quantity', 'label' => 'Кількість'],
                    ['key' => 'created_at', 'label' => 'Створено', 'type' => 'datetime'],
                ],
                'fields' => [
                    ['name' => 'user_id', 'label' => 'Користувач', 'type' => 'select', 'options' => 'users'],
                    ['name' => 'product_id', 'label' => 'Товар', 'type' => 'select', 'options' => 'products'],
                    ['name' => 'quantity', 'label' => 'Кількість', 'type' => 'number', 'placeholder' => '1'],
                ],
                'validation' => static fn (): array => [
                    'user_id' => ['required', 'exists:users,id'],
                    'product_id' => ['required', 'exists:products,id'],
                    'quantity' => ['required', 'integer', 'min:1'],
                ],
            ],
            'orders' => [
                'label' => 'Замовлення',
                'description' => 'Замовлення, статуси та суми',
                'group' => 'Продажі',
                'icon' => 'receipt',
                'domain' => 'operations',
                'model' => Order::class,
                'with' => ['user'],
                'search' => ['full_name', 'email', 'payment_reference'],
                'default_sort' => ['created_at', 'desc'],
                'sorts' => [
                    ['name' => 'created_at', 'label' => 'Спочатку нові', 'column' => 'created_at'],
                    ['name' => 'total', 'label' => 'За сумою', 'column' => 'total'],
                ],
                'filters' => [
                    ['name' => 'status', 'label' => 'Статус замовлення', 'type' => 'select', 'column' => 'status', 'options' => 'order_statuses'],
                    ['name' => 'payment_status', 'label' => 'Статус оплати', 'type' => 'select', 'column' => 'payment_status', 'options' => 'payment_statuses'],
                    ['name' => 'payment_method', 'label' => 'Спосіб оплати', 'type' => 'select', 'column' => 'payment_method', 'options' => 'payment_methods'],
                ],
                'title_key' => 'payment_reference',
                'columns' => [
                    ['key' => 'payment_reference', 'label' => 'Референс'],
                    ['key' => 'full_name', 'label' => 'Клієнт'],
                    ['key' => 'status', 'label' => 'Статус'],
                    ['key' => 'payment_status', 'label' => 'Статус оплати'],
                    ['key' => 'total', 'label' => 'Сума', 'type' => 'money'],
                ],
                'fields' => [
                    ['name' => 'user_id', 'label' => 'Користувач', 'type' => 'select', 'options' => 'users'],
                    ['name' => 'full_name', 'label' => 'ПІБ', 'type' => 'text', 'placeholder' => 'Повне ім’я'],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'placeholder' => 'name@example.com'],
                    ['name' => 'payment_method', 'label' => 'Спосіб оплати', 'type' => 'select', 'options' => 'payment_methods'],
                    ['name' => 'payment_provider', 'label' => 'Провайдер', 'type' => 'text', 'placeholder' => 'demo_card'],
                    ['name' => 'payment_reference', 'label' => 'Платіжний референс', 'type' => 'text', 'placeholder' => 'DIVA-...'],
                    ['name' => 'payment_status', 'label' => 'Статус оплати', 'type' => 'select', 'options' => 'payment_statuses'],
                    ['name' => 'status', 'label' => 'Статус замовлення', 'type' => 'select', 'options' => 'order_statuses'],
                    ['name' => 'total', 'label' => 'Сума', 'type' => 'number', 'placeholder' => '0.00'],
                    ['name' => 'paid_at', 'label' => 'Оплачено о', 'type' => 'datetime-local'],
                    ['name' => 'payment_reconciled_at', 'label' => 'Звірено о', 'type' => 'datetime-local'],
                ],
                'validation' => static fn (?Model $record): array => [
                    'user_id' => ['required', 'exists:users,id'],
                    'full_name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'max:255'],
                    'payment_method' => ['required', Rule::in(['demo_card', 'cash_on_delivery'])],
                    'payment_provider' => ['nullable', 'string', 'max:255'],
                    'payment_reference' => ['nullable', 'string', 'max:255', Rule::unique('orders', 'payment_reference')->ignore($record?->getKey())],
                    'payment_status' => ['required', Rule::in(['pending', 'paid', 'failed', 'cancelled'])],
                    'status' => ['required', Rule::in(['pending', 'paid', 'failed', 'cancelled'])],
                    'total' => ['required', 'numeric', 'min:0'],
                    'paid_at' => ['nullable', 'date'],
                    'payment_reconciled_at' => ['nullable', 'date'],
                ],
            ],
            'order-items' => [
                'label' => 'Позиції замовлень',
                'description' => 'Склад кожного замовлення',
                'group' => 'Продажі',
                'icon' => 'clipboard',
                'domain' => 'operations',
                'model' => OrderItem::class,
                'with' => ['order', 'product'],
                'search' => [],
                'default_sort' => ['created_at', 'desc'],
                'sorts' => [
                    ['name' => 'created_at', 'label' => 'Спочатку нові', 'column' => 'created_at'],
                    ['name' => 'quantity', 'label' => 'За кількістю', 'column' => 'quantity'],
                    ['name' => 'price', 'label' => 'За ціною', 'column' => 'price'],
                ],
                'filters' => [
                    ['name' => 'order_id', 'label' => 'Замовлення', 'type' => 'select', 'column' => 'order_id', 'options' => 'orders'],
                    ['name' => 'product_id', 'label' => 'Товар', 'type' => 'select', 'column' => 'product_id', 'options' => 'products'],
                ],
                'title_key' => 'id',
                'columns' => [
                    ['key' => 'order.payment_reference', 'label' => 'Замовлення'],
                    ['key' => 'product.name', 'label' => 'Товар'],
                    ['key' => 'quantity', 'label' => 'Кількість'],
                    ['key' => 'price', 'label' => 'Ціна', 'type' => 'money'],
                ],
                'fields' => [
                    ['name' => 'order_id', 'label' => 'Замовлення', 'type' => 'select', 'options' => 'orders'],
                    ['name' => 'product_id', 'label' => 'Товар', 'type' => 'select', 'options' => 'products'],
                    ['name' => 'quantity', 'label' => 'Кількість', 'type' => 'number', 'placeholder' => '1'],
                    ['name' => 'price', 'label' => 'Ціна', 'type' => 'number', 'placeholder' => '0.00'],
                ],
                'validation' => static fn (): array => [
                    'order_id' => ['required', 'exists:orders,id'],
                    'product_id' => ['required', 'exists:products,id'],
                    'quantity' => ['required', 'integer', 'min:1'],
                    'price' => ['required', 'numeric', 'min:0'],
                ],
            ],
            'payment-transactions' => [
                'label' => 'Платежі',
                'description' => 'Транзакції та технічні стани оплат',
                'group' => 'Продажі',
                'icon' => 'credit-card',
                'domain' => 'operations',
                'model' => PaymentTransaction::class,
                'with' => ['order'],
                'search' => ['reference', 'provider_reference', 'provider', 'payment_method'],
                'default_sort' => ['created_at', 'desc'],
                'sorts' => [
                    ['name' => 'created_at', 'label' => 'Спочатку нові', 'column' => 'created_at'],
                    ['name' => 'amount', 'label' => 'За сумою', 'column' => 'amount'],
                    ['name' => 'status', 'label' => 'За статусом', 'column' => 'status'],
                ],
                'filters' => [
                    ['name' => 'status', 'label' => 'Статус оплати', 'type' => 'select', 'column' => 'status', 'options' => 'payment_statuses'],
                    ['name' => 'payment_method', 'label' => 'Спосіб оплати', 'type' => 'select', 'column' => 'payment_method', 'options' => 'payment_methods'],
                    ['name' => 'order_id', 'label' => 'Замовлення', 'type' => 'select', 'column' => 'order_id', 'options' => 'orders'],
                ],
                'title_key' => 'reference',
                'columns' => [
                    ['key' => 'reference', 'label' => 'Референс'],
                    ['key' => 'order.payment_reference', 'label' => 'Замовлення'],
                    ['key' => 'status', 'label' => 'Статус'],
                    ['key' => 'amount', 'label' => 'Сума', 'type' => 'money'],
                    ['key' => 'currency', 'label' => 'Валюта'],
                ],
                'fields' => [
                    ['name' => 'order_id', 'label' => 'Замовлення', 'type' => 'select', 'options' => 'orders'],
                    ['name' => 'provider', 'label' => 'Провайдер', 'type' => 'text', 'placeholder' => 'demo_card'],
                    ['name' => 'payment_method', 'label' => 'Спосіб оплати', 'type' => 'select', 'options' => 'payment_methods'],
                    ['name' => 'reference', 'label' => 'Референс', 'type' => 'text', 'placeholder' => 'PAY-...'],
                    ['name' => 'provider_reference', 'label' => 'Референс провайдера', 'type' => 'text', 'placeholder' => 'PROVIDER-...'],
                    ['name' => 'amount', 'label' => 'Сума', 'type' => 'number', 'placeholder' => '0.00'],
                    ['name' => 'currency', 'label' => 'Валюта', 'type' => 'text', 'placeholder' => 'UAH', 'default' => 'UAH'],
                    ['name' => 'status', 'label' => 'Статус', 'type' => 'select', 'options' => 'payment_statuses'],
                    ['name' => 'checkout_url', 'label' => 'Checkout URL', 'type' => 'url', 'placeholder' => 'https://...'],
                    ['name' => 'provider_payload', 'label' => 'Provider payload (JSON)', 'type' => 'textarea', 'placeholder' => '{"meta":true}'],
                    ['name' => 'last_webhook_at', 'label' => 'Останній webhook', 'type' => 'datetime-local'],
                    ['name' => 'reconciled_at', 'label' => 'Звірено о', 'type' => 'datetime-local'],
                    ['name' => 'paid_at', 'label' => 'Оплачено о', 'type' => 'datetime-local'],
                    ['name' => 'failed_at', 'label' => 'Помилка о', 'type' => 'datetime-local'],
                ],
                'validation' => static fn (?Model $record): array => [
                    'order_id' => ['required', 'exists:orders,id'],
                    'provider' => ['required', 'string', 'max:255'],
                    'payment_method' => ['required', Rule::in(['demo_card', 'cash_on_delivery'])],
                    'reference' => ['required', 'string', 'max:255', Rule::unique('payment_transactions', 'reference')->ignore($record?->getKey())],
                    'provider_reference' => ['nullable', 'string', 'max:255'],
                    'amount' => ['required', 'numeric', 'min:0'],
                    'currency' => ['required', 'string', 'size:3'],
                    'status' => ['required', Rule::in(['pending', 'paid', 'failed', 'cancelled'])],
                    'checkout_url' => ['nullable', 'url', 'max:2048'],
                    'provider_payload' => ['nullable', 'string'],
                    'last_webhook_at' => ['nullable', 'date'],
                    'reconciled_at' => ['nullable', 'date'],
                    'paid_at' => ['nullable', 'date'],
                    'failed_at' => ['nullable', 'date'],
                ],
            ],
        ];
    }

    private function fieldValue(Model $record, array $field): mixed
    {
        $value = data_get($record, $field['name']);

        if ($value === null) {
            return '';
        }

        if (($field['type'] ?? null) === 'datetime-local') {
            return $record->{$field['name']}?->format('Y-m-d\TH:i');
        }

        if ($field['name'] === 'provider_payload' && is_array($value)) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        return $value;
    }

    private function resolveOptions(string $key): array
    {
        return match ($key) {
            'staff_roles' => MoonshineUserRole::query()->orderBy('name')->get()
                ->map(fn (MoonshineUserRole $role): array => ['value' => $role->id, 'label' => $role->name])
                ->all(),
            'categories' => Category::query()->orderBy('name')->get()
                ->map(fn (Category $category): array => ['value' => $category->id, 'label' => $category->name])
                ->all(),
            'products' => Product::query()->orderBy('name')->get()
                ->map(fn (Product $product): array => ['value' => $product->id, 'label' => $product->name])
                ->all(),
            'users' => User::query()->orderBy('name')->get()
                ->map(fn (User $user): array => ['value' => $user->id, 'label' => "{$user->name} ({$user->email})"])
                ->all(),
            'orders' => Order::query()->orderByDesc('created_at')->get()
                ->map(fn (Order $order): array => [
                    'value' => $order->id,
                    'label' => trim(sprintf('#%d %s', $order->id, $order->payment_reference ?? $order->full_name)),
                ])->all(),
            'payment_methods' => [
                ['value' => 'demo_card', 'label' => 'Демо-картка'],
                ['value' => 'cash_on_delivery', 'label' => 'Післяплата'],
            ],
            'payment_statuses' => [
                ['value' => 'pending', 'label' => 'Очікується'],
                ['value' => 'paid', 'label' => 'Сплачено'],
                ['value' => 'failed', 'label' => 'Помилка'],
                ['value' => 'cancelled', 'label' => 'Скасовано'],
            ],
            'order_statuses' => [
                ['value' => 'pending', 'label' => 'В очікуванні'],
                ['value' => 'paid', 'label' => 'Сплачено'],
                ['value' => 'failed', 'label' => 'Помилка'],
                ['value' => 'cancelled', 'label' => 'Скасовано'],
            ],
            'sort_directions' => [
                ['value' => 'desc', 'label' => 'За спаданням'],
                ['value' => 'asc', 'label' => 'За зростанням'],
            ],
            default => [],
        };
    }

    private function formatValue(mixed $value, string $type): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return match ($type) {
            'money' => number_format((float) $value, 2, '.', ' ').' ₴',
            'datetime' => method_exists($value, 'format')
                ? $value->format('d.m.Y H:i')
                : (string) $value,
            default => is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE),
        };
    }
}
