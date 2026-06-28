<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Models\ExamSubject;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Subject mapping for a session (reuses Academic subjects; never duplicated). */
class ExamSubjectService extends BaseCrudService
{
    protected function model(): string
    {
        return ExamSubject::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'subject:id,name,code',
            'schoolClass:id,name',
            'section:id,name',
            'subjectType:id,label,value',
        ]);
    }

    protected function filterable(): array
    {
        return ['school_id', 'exam_session_id', 'class_id', 'section_id', 'subject_id', 'is_elective', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'sort_order', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'subject' => ['type' => 'relation', 'relation' => 'subject', 'columns' => ['name', 'code']],
        ];
    }
}
