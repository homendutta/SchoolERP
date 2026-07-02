<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Services;

use App\Modules\Hostel\Enums\AllocationStatus;
use App\Modules\Hostel\Models\Allocation;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Read + search over bed allocations (writes go through the engine). */
class AllocationService extends BaseCrudService
{
    protected function model(): string
    {
        return Allocation::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'student:id,name,admission_number,identity_id',
            'hostel:id,name',
            'room:id,room_number',
            'bed:id,bed_number,bed_code',
        ]);
    }

    protected function filterable(): array
    {
        return ['school_id', 'student_id', 'academic_year_id', 'hostel_id', 'room_id', 'bed_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'allocation_date', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'status' => ['type' => 'enum', 'enum' => AllocationStatus::class],
            'student' => ['type' => 'relation', 'relation' => 'student', 'columns' => ['name', 'admission_number']],
        ];
    }
}
