<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminPanel\AdminResourceRegistry;
use App\Support\BackofficeAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ResourceController extends Controller
{
    public function __construct(
        private readonly AdminResourceRegistry $registry,
        private readonly BackofficeAccess $backofficeAccess,
    ) {}

    public function index(Request $request, string $resource): Response
    {
        $user = $this->backofficeUser();
        $resourceConfig = $this->resource($resource);

        $this->ensureResourcePermission($user, $resourceConfig, 'viewAny');

        $filters = array_filter(
            [
                'search' => trim((string) $request->string('search')->value()),
                'sort' => trim((string) $request->string('sort')->value()),
                'direction' => trim((string) $request->string('direction')->value()),
                ...collect($resourceConfig['filters'] ?? [])->mapWithKeys(
                    fn (array $filter): array => [$filter['name'] => trim((string) $request->input($filter['name'], ''))]
                )->all(),
            ],
            static fn ($value): bool => $value !== ''
        );

        $records = $this->registry
            ->queryForIndex($resourceConfig, $filters)
            ->paginate(12)
            ->through(fn ($record): array => $this->registry->formatRecord($record, $resourceConfig))
            ->withQueryString();

        $schema = $this->registry->indexSchema($resourceConfig);

        return Inertia::render('Admin/Resources/Index', [
            'navigation' => $this->navigationFor($user),
            'resource' => [
                'key' => $resourceConfig['key'],
                'label' => $resourceConfig['label'],
                'description' => $resourceConfig['description'],
                'columns' => $resourceConfig['columns'],
                'canSearch' => ! empty($resourceConfig['search']),
                'sorts' => $schema['sorts'],
                'filters' => $schema['filters'],
                'permissions' => $this->permissionsFor($user, $resourceConfig),
            ],
            'filters' => $filters,
            'records' => $records,
        ]);
    }

    public function create(string $resource): Response
    {
        $user = $this->backofficeUser();
        $resourceConfig = $this->resource($resource);

        $this->ensureResourcePermission($user, $resourceConfig, 'create');

        return Inertia::render('Admin/Resources/Form', [
            'navigation' => $this->navigationFor($user),
            'resource' => [
                'key' => $resourceConfig['key'],
                'label' => $resourceConfig['label'],
                'description' => $resourceConfig['description'],
                'permissions' => $this->permissionsFor($user, $resourceConfig),
            ],
            'mode' => 'create',
            'record' => null,
            'fields' => $this->registry->fields($resourceConfig),
        ]);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $user = $this->backofficeUser();
        $resourceConfig = $this->resource($resource);

        $this->ensureResourcePermission($user, $resourceConfig, 'create');

        $data = $this->registry->validateAndTransform($request, $resourceConfig);
        $modelClass = $this->registry->modelClass($resourceConfig);
        $modelClass::query()->create($data);

        return redirect()
            ->route('admin.resources.index', $resourceConfig['key'])
            ->with('message', "{$resourceConfig['label']} успішно створено.");
    }

    public function edit(string $resource, int $record): Response
    {
        $user = $this->backofficeUser();
        $resourceConfig = $this->resource($resource);

        $this->ensureResourcePermission($user, $resourceConfig, 'update');

        $model = $this->findRecord($resourceConfig, $record);

        return Inertia::render('Admin/Resources/Form', [
            'navigation' => $this->navigationFor($user),
            'resource' => [
                'key' => $resourceConfig['key'],
                'label' => $resourceConfig['label'],
                'description' => $resourceConfig['description'],
                'permissions' => $this->permissionsFor($user, $resourceConfig),
            ],
            'mode' => 'edit',
            'record' => [
                'id' => $model->getKey(),
                'title' => $this->registry->recordTitle($model, $resourceConfig),
            ],
            'fields' => $this->registry->fields($resourceConfig, $model),
        ]);
    }

    public function update(Request $request, string $resource, int $record): RedirectResponse
    {
        $user = $this->backofficeUser();
        $resourceConfig = $this->resource($resource);

        $this->ensureResourcePermission($user, $resourceConfig, 'update');

        $model = $this->findRecord($resourceConfig, $record);
        $data = $this->registry->validateAndTransform($request, $resourceConfig, $model);
        $model->fill($data)->save();

        return redirect()
            ->route('admin.resources.index', $resourceConfig['key'])
            ->with('message', "{$resourceConfig['label']} успішно оновлено.");
    }

    public function destroy(string $resource, int $record): RedirectResponse
    {
        $user = $this->backofficeUser();
        $resourceConfig = $this->resource($resource);

        $this->ensureResourcePermission($user, $resourceConfig, 'delete');

        $this->findRecord($resourceConfig, $record)->delete();

        return redirect()
            ->route('admin.resources.index', $resourceConfig['key'])
            ->with('message', "{$resourceConfig['label']} успішно видалено.");
    }

    private function resource(string $key): array
    {
        return $this->registry->get($key);
    }

    private function findRecord(array $resource, int $recordId)
    {
        $modelClass = $this->registry->modelClass($resource);

        return $modelClass::query()
            ->with($resource['with'] ?? [])
            ->findOrFail($recordId);
    }

    private function ensureResourcePermission($user, array $resource, string $ability): void
    {
        abort_unless(
            $this->backofficeAccess->canAccessAdminDomain($user, $resource['domain'], $ability),
            403
        );
    }

    private function backofficeUser()
    {
        return Auth::guard(config('moonshine.auth.guard', 'moonshine'))->user();
    }

    private function permissionsFor($user, array $resource): array
    {
        return [
            'create' => $this->backofficeAccess->canAccessAdminDomain($user, $resource['domain'], 'create'),
            'update' => $this->backofficeAccess->canAccessAdminDomain($user, $resource['domain'], 'update'),
            'delete' => $this->backofficeAccess->canAccessAdminDomain($user, $resource['domain'], 'delete'),
        ];
    }

    private function navigationFor($user): array
    {
        return collect($this->registry->navigation())->map(function (array $group) use ($user): array {
            return [
                'label' => $group['label'],
                'items' => collect($group['items'])
                    ->filter(fn (array $item): bool => $this->backofficeAccess->canAccessAdminDomain($user, $item['domain'], 'viewAny'))
                    ->values()
                    ->all(),
            ];
        })->filter(fn (array $group): bool => $group['items'] !== [])->values()->all();
    }
}
