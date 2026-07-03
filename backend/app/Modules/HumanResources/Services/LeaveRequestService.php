<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Services;

use App\Modules\HumanResources\Enums\LeaveStatus;
use App\Modules\HumanResources\Models\LeaveRequest;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Read + search over leave requests. All WRITES go through the Leave Engine; this
 * service only lists/finds (with the approval trail eager-loaded).
 */
class LeaveRequestService extends BaseCrudService
{
    protected function model(): string
    {
        return LeaveRequest::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'employee:id,name,employee_number',
            'leaveType:id,name',
            'approvals',
        ]);
    }

    protected function filterable(): array
    {
        return ['school_id', 'staff_id', 'leave_type_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'start_date', 'applied_on', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'status' => ['type' => 'enum', 'enum' => LeaveStatus::class],
        ];
    }

    public function find(int|string $id): Model
    {
        $query = $this->query();
        $this->withRelations($query);

        return $query->findOrFail($id);
    }
}
