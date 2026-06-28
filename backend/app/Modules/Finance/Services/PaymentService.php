<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Enums\PaymentStatus;
use App\Modules\Finance\Models\Payment;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Read + search over payments. */
class PaymentService extends BaseCrudService
{
    protected function model(): string
    {
        return Payment::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with(['student:id,name,admission_number,identity_id', 'method:id,label', 'allocations']);
    }

    protected function searchable(): array
    {
        return ['receipt_number', 'transaction_number', 'reference'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'student_id', 'payment_method_id', 'status', 'paid_on'];
    }

    protected function sortable(): array
    {
        return ['id', 'paid_on', 'amount'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'status' => ['type' => 'enum', 'enum' => PaymentStatus::class],
            'paid_on' => ['type' => 'date'],
            'student' => ['type' => 'relation', 'relation' => 'student', 'columns' => ['name', 'admission_number']],
        ];
    }
}
