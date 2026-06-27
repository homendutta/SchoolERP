<?php

declare(strict_types=1);

namespace App\Platform\Shared\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

/**
 * Contract every reusable CRUD service fulfils (implemented by BaseCrudService).
 */
interface CrudService
{
    /** @param array<string, mixed> $params */
    public function list(array $params): LengthAwarePaginator;

    public function find(int|string $id): Model;

    /** @param array<string, mixed> $data */
    public function create(array $data): Model;

    /** @param array<string, mixed> $data */
    public function update(Model $model, array $data): Model;

    public function delete(Model $model): bool;

    public function archive(Model $model): bool;

    public function restore(Model $model): bool;

    /** @param array<int, int|string> $ids */
    public function bulkDelete(array $ids): int;
}
