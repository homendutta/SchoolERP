<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Modules\Cms\Enums\ContentStatus;
use App\Modules\Cms\Enums\VideoProvider;
use App\Modules\Cms\Models\Video;
use Illuminate\Database\Eloquent\Builder;

class VideoService extends ContentService
{
    protected function model(): string
    {
        return Video::class;
    }

    protected function contentType(): string
    {
        return 'video';
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
        return ['school_id', 'category_id', 'provider', 'status', 'featured'];
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
            'provider' => ['type' => 'enum', 'enum' => VideoProvider::class],
            'status' => ['type' => 'enum', 'enum' => ContentStatus::class],
        ];
    }
}
