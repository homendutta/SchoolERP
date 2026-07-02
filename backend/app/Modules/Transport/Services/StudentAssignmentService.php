<?php

declare(strict_types=1);

namespace App\Modules\Transport\Services;

use App\Modules\Transport\Enums\AssignmentStatus;
use App\Modules\Transport\Models\StudentAssignment;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Read + search over student transport assignments (write via the engine). */
class StudentAssignmentService extends BaseCrudService
{
    protected function model(): string
    {
        return StudentAssignment::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'student:id,name,admission_number',
            'route:id,name,route_code',
            'stop:id,name,sequence',
        ]);
    }

    protected function filterable(): array
    {
        return ['school_id', 'student_id', 'route_id', 'stop_id', 'academic_year_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'assigned_on', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'status' => ['type' => 'enum', 'enum' => AssignmentStatus::class],
            'student' => ['type' => 'relation', 'relation' => 'student', 'columns' => ['name', 'admission_number']],
        ];
    }
}
