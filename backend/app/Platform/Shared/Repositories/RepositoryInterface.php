<?php

declare(strict_types=1);

namespace App\Platform\Shared\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Contract for the Repository layer.
 *
 * Repositories are the ONLY place that touches persistence. Services depend on
 * this interface (never on Eloquent directly), keeping business logic
 * persistence-agnostic and testable. Module repositories bind their concrete
 * implementation to a module-specific interface in their service provider.
 *
 * No table or schema is defined here — only the data-access contract.
 */
interface RepositoryInterface
{
    /** @param array<int, string> $columns */
    public function all(array $columns = ['*']): Collection;

    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function find(int|string $id): ?Model;

    public function findOrFail(int|string $id): Model;

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Model;

    /** @param array<string, mixed> $attributes */
    public function update(Model $model, array $attributes): Model;

    public function delete(Model $model): bool;
}
