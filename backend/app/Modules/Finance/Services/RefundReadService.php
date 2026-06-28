<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Models\Refund;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

class RefundReadService extends BaseCrudService
{
    protected function model(): string
    {
        return Refund::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['student:id,name,admission_number', 'payment:id,receipt_number']);
    }

    protected function searchable(): array
    {
        return ['transaction_number', 'reason'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'student_id', 'payment_id', 'type', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'refunded_on', 'amount'];
    }
}
