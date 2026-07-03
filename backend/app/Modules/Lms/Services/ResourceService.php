<?php

declare(strict_types=1);

namespace App\Modules\Lms\Services;

use App\Modules\Lms\Enums\LmsStatus;
use App\Modules\Lms\Models\Resource;

class ResourceService extends LmsContentService
{
    protected function model(): string
    {
        return Resource::class;
    }

    protected function contentType(): string
    {
        return 'resource';
    }

    protected function searchable(): array
    {
        return ['title', 'topic'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'subject_id', 'class_id', 'teacher_id', 'type', 'status'];
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
            'title' => ['type' => 'text', 'columns' => ['title', 'topic']],
            'status' => ['type' => 'enum', 'enum' => LmsStatus::class],
        ];
    }
}
