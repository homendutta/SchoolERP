<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Modules\Cms\Enums\ContentStatus;
use App\Modules\Cms\Models\News;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NewsService extends ContentService
{
    protected function model(): string
    {
        return News::class;
    }

    protected function contentType(): string
    {
        return 'news';
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['category:id,name']);
    }

    protected function searchable(): array
    {
        return ['title', 'excerpt'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'category_id', 'status', 'featured'];
    }

    protected function sortable(): array
    {
        return ['id', 'publish_date', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'title' => ['type' => 'text', 'columns' => ['title']],
            'status' => ['type' => 'enum', 'enum' => ContentStatus::class],
        ];
    }

    protected function onPublished(Model $model): void
    {
        $this->hooks->newsPublished((int) $model->getAttribute('school_id'), 'News: '.$model->getAttribute('title'));
    }
}
