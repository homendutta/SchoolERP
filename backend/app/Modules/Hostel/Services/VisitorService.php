<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Services;

use App\Modules\Hostel\Enums\VisitorStatus;
use App\Modules\Hostel\Models\Visitor;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Hostel visitors (ID proof via Media). */
class VisitorService extends BaseCrudService
{
    protected function model(): string
    {
        return Visitor::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['student:id,name,admission_number']);
    }

    protected function searchable(): array
    {
        return ['visitor_name', 'purpose'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'hostel_id', 'student_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'visit_date', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'status' => ['type' => 'enum', 'enum' => VisitorStatus::class],
            'visit_date' => ['type' => 'date'],
            'visitor' => ['type' => 'text', 'columns' => ['visitor_name']],
        ];
    }
}
