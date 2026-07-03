<?php

declare(strict_types=1);

namespace App\Modules\Lms\Services;

use App\Modules\Lms\Enums\LmsStatus;
use App\Modules\Lms\Models\Lesson;
use Illuminate\Database\Eloquent\Model;

class LessonService extends LmsContentService
{
    protected function model(): string
    {
        return Lesson::class;
    }

    protected function contentType(): string
    {
        return 'lesson';
    }

    protected function searchable(): array
    {
        return ['title'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'lesson_plan_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'created_at'];
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
        $this->hooks->publish((int) $model->getAttribute('school_id'), 'lms.lesson_published', 'Lesson published', 'Lesson: '.$model->getAttribute('title'));
    }
}
