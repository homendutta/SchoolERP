<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Modules\Cms\Enums\ContentStatus;
use App\Modules\Cms\Models\Page;

class PageService extends ContentService
{
    protected function model(): string
    {
        return Page::class;
    }

    protected function contentType(): string
    {
        return 'page';
    }

    protected function searchable(): array
    {
        return ['title', 'slug'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'status'];
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
            'title' => ['type' => 'text', 'columns' => ['title', 'slug']],
            'status' => ['type' => 'enum', 'enum' => ContentStatus::class],
        ];
    }
}
