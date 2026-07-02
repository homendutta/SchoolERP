<?php

declare(strict_types=1);

namespace App\Modules\Library\Services;

use App\Modules\Library\Models\Book;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/** The catalog. Authors are synced as a many-to-many (never comma-separated). */
class BookService extends BaseCrudService
{
    protected function model(): string
    {
        return Book::class;
    }

    /** Eager-load relations on single-record reads (show). */
    public function find(int|string $id): Model
    {
        $query = $this->query();
        $this->withRelations($query);

        return $query->findOrFail($id);
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['authors:id,name', 'publisher:id,name', 'category:id,name'])->withCount('copies');
    }

    protected function searchable(): array
    {
        return ['title', 'subtitle', 'isbn'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'publisher_id', 'category_id', 'language', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'title', 'publication_year', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'title' => ['type' => 'text', 'columns' => ['title', 'subtitle']],
            'isbn' => ['type' => 'text', 'columns' => ['isbn']],
            'author' => ['type' => 'relation', 'relation' => 'authors', 'columns' => ['name']],
            'publisher' => ['type' => 'relation', 'relation' => 'publisher', 'columns' => ['name']],
            'category' => ['type' => 'relation', 'relation' => 'category', 'columns' => ['name']],
        ];
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): Model
    {
        return $this->transaction(function () use ($data): Model {
            $authorIds = $data['author_ids'] ?? [];
            unset($data['author_ids']);
            $book = Book::query()->create($data);
            $book->authors()->sync((array) $authorIds);

            return $book->load('authors');
        });
    }

    /** @param array<string, mixed> $data */
    public function update(Model $model, array $data): Model
    {
        return $this->transaction(function () use ($model, $data): Model {
            $authorIds = $data['author_ids'] ?? null;
            unset($data['author_ids']);
            $model->fill($data)->save();
            if ($authorIds !== null) {
                $model->authors()->sync((array) $authorIds);
            }

            return $model->load('authors');
        });
    }
}
