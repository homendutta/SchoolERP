<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Services;

use App\Modules\HumanResources\Models\EmployeeDocument;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Employee documents — Media references only (Media Platform owns the files). */
class EmployeeDocumentService extends BaseCrudService
{
    protected function model(): string
    {
        return EmployeeDocument::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['employee:id,name,employee_number']);
    }

    protected function searchable(): array
    {
        return ['title'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'staff_id', 'document_type'];
    }

    protected function sortable(): array
    {
        return ['id', 'issued_date', 'expiry_date'];
    }
}
