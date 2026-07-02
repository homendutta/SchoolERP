<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Services;

use App\Modules\Hostel\Models\Warden;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Warden (Staff) assignments to hostels. */
class WardenService extends BaseCrudService
{
    protected function model(): string
    {
        return Warden::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['staff:id,name,employee_number', 'hostel:id,name']);
    }

    protected function filterable(): array
    {
        return ['school_id', 'hostel_id', 'staff_id', 'role', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'role'];
    }
}
