<?php

declare(strict_types=1);

namespace App\Platform\Shared\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Abstract Eloquent repository.
 *
 * Provides generic, reusable data-access operations so module repositories can
 * focus on module-specific queries. Concrete repositories implement model() to
 * return their aggregate root's Eloquent model class.
 *
 * Generic scaffolding only: defines NO tables, columns, or business queries.
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * The fully-qualified Eloquent model class this repository manages.
     *
     * @return class-string<Model>
     */
    abstract protected function model(): string;

    /**
     * A fresh query builder for the managed model.
     */
    protected function query(): Builder
    {
        $model = $this->model();

        return (new $model)->newQuery();
    }

    /** @param array<int, string> $columns */
    public function all(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage);
    }

    public function find(int|string $id): ?Model
    {
        return $this->query()->find($id);
    }

    public function findOrFail(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): Model
    {
        return $this->query()->create($attributes);
    }

    /** @param array<string, mixed> $attributes */
    public function update(Model $model, array $attributes): Model
    {
        $model->fill($attributes)->save();

        return $model->refresh();
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }
}
