<?php

declare(strict_types=1);

namespace App\Platform\Shared\Services;

use App\Platform\Shared\Query\FilterBuilder;
use App\Platform\Shared\Query\SearchBuilder;
use App\Platform\Shared\Query\SortBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

/**
 * Reusable CRUD service. Provides list (search + filter + sort + paginate),
 * create/update/delete, and archive/restore/bulk-delete so no module
 * duplicates CRUD logic. Concrete services declare the model and whitelists.
 */
abstract class BaseCrudService extends BaseService
{
    /** @return class-string<Model> */
    abstract protected function model(): string;

    /** @return array<int, string> columns searched by the `q` param */
    protected function searchable(): array
    {
        return [];
    }

    /** @return array<int, string> columns allowed in `filter[...]` */
    protected function filterable(): array
    {
        return [];
    }

    /** @return array<int, string> columns allowed in `sort` */
    protected function sortable(): array
    {
        return ['id'];
    }

    /**
     * Typed advanced-search definitions applied from the `search` param.
     *
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [];
    }

    protected function query(): Builder
    {
        $model = $this->model();

        return (new $model)->newQuery();
    }

    /**
     * Paginated, searched, filtered, sorted listing.
     *
     * @param  array<string, mixed>  $params
     */
    public function list(array $params): LengthAwarePaginator
    {
        $query = $this->query();

        $this->withRelations($query);

        $query = SearchBuilder::make($query)
            ->text($this->searchable(), $params['q'] ?? null)
            ->applyDefinitions($this->searchDefinitions(), (array) ($params['search'] ?? []))
            ->build();

        FilterBuilder::apply($query, (array) ($params['filter'] ?? []), $this->filterable());
        SortBuilder::apply($query, $params['sort'] ?? null, $this->sortable());

        if (($params['archived'] ?? null) === 'only' && $this->usesSoftDeletes()) {
            $query->onlyTrashed();
        } elseif (($params['archived'] ?? null) === 'with' && $this->usesSoftDeletes()) {
            $query->withTrashed();
        }

        $perPage = (int) ($params['per_page'] ?? 15);

        return $query->paginate($perPage > 0 ? min($perPage, 100) : 15);
    }

    /** Hook for eager loading; override per service. */
    protected function withRelations(Builder $query): void {}

    public function find(int|string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            $model = $this->model();

            return $model::query()->create($data);
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Model $model, array $data): Model
    {
        return $this->transaction(function () use ($model, $data): Model {
            // Optimistic locking: when the record carries a `version` and the
            // client sends the version it edited, reject stale writes.
            if (array_key_exists('version', $data) && $model->getAttribute('version') !== null) {
                if ((int) $data['version'] !== (int) $model->getAttribute('version')) {
                    throw new \App\Platform\Shared\Exceptions\DomainException(
                        'This record was changed by someone else. Please reload and try again.',
                        409,
                        'STALE_VERSION',
                    );
                }
                unset($data['version']);
                $model->setAttribute('version', (int) $model->getAttribute('version') + 1);
            }

            $model->fill($data)->save();

            return $model->refresh();
        });
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    /** Archive = soft delete. */
    public function archive(Model $model): bool
    {
        return $this->delete($model);
    }

    public function restore(Model $model): bool
    {
        if (method_exists($model, 'restore')) {
            return (bool) $model->restore();
        }

        return false;
    }

    /** Find a (possibly soft-deleted) record by id and restore it. */
    public function restoreById(int|string $id): bool
    {
        $query = $this->query();
        if ($this->usesSoftDeletes()) {
            $query->withTrashed();
        }

        return $this->restore($query->findOrFail($id));
    }

    /**
     * @param  array<int, int|string>  $ids
     * @return int number affected
     */
    public function bulkDelete(array $ids): int
    {
        if ($ids === []) {
            return 0;
        }

        return (int) DB::transaction(fn () => $this->query()->whereIn('id', $ids)->delete());
    }

    protected function usesSoftDeletes(): bool
    {
        $model = $this->model();

        return in_array(
            SoftDeletes::class,
            class_uses_recursive($model),
            true
        );
    }
}
