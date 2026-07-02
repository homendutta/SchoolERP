<?php

declare(strict_types=1);

namespace App\Modules\Library\Services;

use App\Modules\Library\Enums\BorrowStatus;
use App\Modules\Library\Models\Borrowing;
use App\Platform\Shared\Services\BaseCrudService;
use Illuminate\Database\Eloquent\Builder;

/** Read + search over borrowing transactions. */
class BorrowingService extends BaseCrudService
{
    protected function model(): string
    {
        return Borrowing::class;
    }

    protected function withRelations(Builder $query): void
    {
        $query->with([
            'book:id,title,isbn',
            'copy:id,copy_number',
            'owner',
            'identity:id,identity_number',
            'renewals:id,borrowing_id,new_due_date,renewed_on',
        ]);
    }

    protected function filterable(): array
    {
        return ['school_id', 'identity_id', 'owner_type', 'copy_id', 'book_id', 'status'];
    }

    protected function sortable(): array
    {
        return ['id', 'borrow_date', 'due_date', 'created_at'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'status' => ['type' => 'enum', 'enum' => BorrowStatus::class],
            'borrow_date' => ['type' => 'date'],
            'borrower' => ['type' => 'relation', 'relation' => 'identity', 'columns' => ['identity_number']],
        ];
    }

    /** Recompute overdue flags for open borrowings past due (read-time helper). */
    public function markOverdue(int $schoolId): int
    {
        return Borrowing::query()
            ->where('school_id', $schoolId)
            ->where('status', BorrowStatus::Borrowed->value)
            ->whereDate('due_date', '<', now()->toDateString())
            ->update(['status' => BorrowStatus::Overdue->value]);
    }
}
