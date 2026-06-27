<?php

declare(strict_types=1);

namespace App\Platform\Shared\Http\Controllers;

use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Reusable CRUD controller. Thin: it delegates to a BaseCrudService and wraps
 * results in the standard envelope + a Resource. Concrete controllers declare
 * the service and resource and provide validated input via Form Requests.
 */
abstract class BaseCrudController extends BaseController
{
    abstract protected function service(): BaseCrudService;

    /** @return class-string<JsonResource> */
    abstract protected function resourceClass(): string;

    public function index(Request $request): JsonResponse
    {
        $page = $this->service()->list($request->all());
        $resource = $this->resourceClass();

        return $this->ok($resource::collection($page), null, 200, [
            'total' => $page->total(),
            'per_page' => $page->perPage(),
            'current_page' => $page->currentPage(),
            'last_page' => $page->lastPage(),
        ]);
    }

    public function show(int|string $id): JsonResponse
    {
        $resource = $this->resourceClass();

        return $this->ok(new $resource($this->service()->find($id)));
    }

    /** @param array<string, mixed> $data */
    protected function created(array $data): JsonResponse
    {
        $resource = $this->resourceClass();

        return $this->ok(new $resource($this->service()->create($data)), 'Created.', 201);
    }

    /** @param array<string, mixed> $data */
    protected function updated(int|string $id, array $data): JsonResponse
    {
        $resource = $this->resourceClass();
        $model = $this->service()->find($id);

        return $this->ok(new $resource($this->service()->update($model, $data)), 'Updated.');
    }

    public function destroy(int|string $id): JsonResponse
    {
        $this->service()->delete($this->service()->find($id));

        return $this->ok(null, 'Deleted.');
    }

    public function archive(int|string $id): JsonResponse
    {
        $this->service()->archive($this->service()->find($id));

        return $this->ok(null, 'Archived.');
    }

    public function restore(int|string $id): JsonResponse
    {
        $this->service()->restoreById($id);

        return $this->ok(null, 'Restored.');
    }

    public function bulkDestroy(Request $request): JsonResponse
    {
        $count = $this->service()->bulkDelete((array) $request->input('ids', []));

        return $this->ok(['deleted' => $count], "{$count} record(s) deleted.");
    }
}
