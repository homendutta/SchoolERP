<?php

declare(strict_types=1);

namespace App\Modules\Examination\Services;

use App\Modules\Examination\Enums\ResultStatus;
use App\Modules\Examination\Models\ExamResult;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Read + search over processed results. */
class ResultService extends BaseCrudService
{
    protected function model(): string
    {
        return ExamResult::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'student:id,name,admission_number,identity_id',
            'grade:id,code,name',
            'schoolClass:id,name',
            'section:id,name',
        ]);
    }

    protected function filterable(): array
    {
        return ['school_id', 'exam_session_id', 'class_id', 'section_id', 'result_status', 'is_published'];
    }

    protected function sortable(): array
    {
        return ['id', 'rank', 'percentage', 'total_obtained'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'result_status' => ['type' => 'enum', 'enum' => ResultStatus::class],
            'student' => ['type' => 'relation', 'relation' => 'student', 'columns' => ['name', 'admission_number']],
        ];
    }
}
