<?php

declare(strict_types=1);

namespace App\Modules\Cms\Services;

use App\Modules\Cms\Enums\ContentStatus;
use App\Modules\Cms\Enums\NoticePriority;
use App\Modules\Cms\Models\Notice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class NoticeService extends ContentService
{
    protected function model(): string
    {
        return Notice::class;
    }

    protected function contentType(): string
    {
        return 'notice';
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
        return ['school_id', 'category_id', 'priority', 'status', 'featured'];
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
            'priority' => ['type' => 'enum', 'enum' => NoticePriority::class],
            'status' => ['type' => 'enum', 'enum' => ContentStatus::class],
        ];
    }

    protected function onPublished(Model $model): void
    {
        $this->hooks->noticePublished((int) $model->getAttribute('school_id'), 'Notice: '.$model->getAttribute('title'));
    }
}
