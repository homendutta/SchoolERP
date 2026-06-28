<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Enums\ExamSessionStatus;
use App\Modules\Examination\Models\ExamSession;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class ExamSessionService extends BaseCrudService
{
    protected function model(): string
    {
        return ExamSession::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'examType:id,name',
            'academicYear:id,name',
            'term:id,name',
        ]);
    }

    protected function searchable(): array
    {
        return ['name', 'description'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'academic_year_id', 'term_id', 'exam_type_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'start_date', 'name', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'status' => ['type' => 'enum', 'enum' => ExamSessionStatus::class],
            'start_date' => ['type' => 'date'],
        ];
    }
}
