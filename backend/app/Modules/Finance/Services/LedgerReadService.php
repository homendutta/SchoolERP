<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Enums\LedgerEntryType;
use App\Modules\Finance\Models\LedgerEntry;
use App\Platform\Shared\Services\BaseCrudService;

/** Read + search over the independent financial ledger. */
class LedgerReadService extends BaseCrudService
{
    protected function model(): string
    {
        return LedgerEntry::class;
    }

    protected function searchable(): array
    {
        return ['narration'];
    }

    protected function filterable(): array
    {
        return ['school_id', 'student_id', 'entry_type', 'source_type'];
    }

    protected function sortable(): array
    {
        return ['id', 'entry_date', 'amount'];
    }

    /**
     * @return array<string, array{type:string, columns?:array<int,string>, enum?:string, relation?:string}>
     */
    protected function searchDefinitions(): array
    {
        return [
            'entry_type' => ['type' => 'enum', 'enum' => LedgerEntryType::class],
            'entry_date' => ['type' => 'date'],
        ];
    }
}
