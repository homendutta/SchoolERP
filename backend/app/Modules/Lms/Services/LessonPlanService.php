<?php

declare(strict_types=1);

namespace App\Modules\Lms\Services;

use App\Modules\Lms\Enums\LmsStatus;
use App\Modules\Lms\Models\LessonPlan;

class LessonPlanService extends LmsContentService
{
    protected function model(): string
    {
        return LessonPlan::class;
    }

    protected function contentType(): string
    {
        return 'lesson_plan';
    }

    protected function searchable(): array
    {
        return ['title'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'subject_id', 'class_id', 'section_id', 'teacher_id', 'status', 'completion_status'];
    }

    protected function sortable(): array
    {
        return ['id', 'planned_date', 'created_at'];
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
}
