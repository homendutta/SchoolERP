<?php

declare(strict_types=1);

namespace App\Modules\Lms\Services;

use App\Modules\Lms\Enums\LmsStatus;
use App\Modules\Lms\Models\Assignment;
use Illuminate\Database\Eloquent\Model;

class AssignmentService extends LmsContentService
{
    protected function model(): string
    {
        return Assignment::class;
    }

    protected function contentType(): string
    {
        return 'assignment';
    }

    protected function searchable(): array
    {
        return ['title'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'class_id', 'section_id', 'subject_id', 'teacher_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'due_date', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'title' => ['type' => 'text', 'columns' => ['title']],
            'status' => ['type' => 'enum', 'enum' => LmsStatus::class],
        ];
    }

    protected function onPublished(Model $model): void
    {
        $this->hooks->publish((int) $model->getAttribute('school_id'), 'lms.assignment_published', 'Assignment published', 'Assignment: '.$model->getAttribute('title'));
    }
}
