<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Modules\Cms\Enums\ContentStatus;
use App\Modules\Cms\Models\Download;
use Illuminate\Database\Eloquent\Builder;

class DownloadService extends ContentService
{
    protected function model(): string
    {
        return Download::class;
    }

    protected function contentType(): string
    {
        return 'download';
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['category:id,name']);
    }

    protected function searchable(): array
    {
        return ['title'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'category_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'title', 'created_at'];
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
}
