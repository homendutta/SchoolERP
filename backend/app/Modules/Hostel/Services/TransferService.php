<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Services;

use App\Modules\Hostel\Models\Transfer;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Read over transfer events (writes go through the engine). */
class TransferService extends BaseCrudService
{
    protected function model(): string
    {
        return Transfer::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['student:id,name,admission_number']);
    }

    protected function filterable(): array
    {
        return ['school_id', 'student_id', 'transfer_type'];
    }

    protected function sortable(): array
    {
        return ['id', 'transfer_date', 'created_at'];
    }
}
