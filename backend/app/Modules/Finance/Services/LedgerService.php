<?php

declare(strict_types=1);

namespace App\Modules\Finance\Services;

use App\Modules\Finance\Enums\LedgerEntryType;
use App\Modules\Finance\Models\LedgerEntry;
use Illuminate\Database\Eloquent\Model;

/**
 * The Financial Ledger. Entries are generated automatically from payments,
 * refunds and adjustments and remain INDEPENDENT of those records — writing a
 * ledger entry never modifies the originating payment.
 */
class LedgerService
{
    public function record(
        Model $source,
        LedgerEntryType $type,
        float $amount,
        string $narration,
        ?int $studentId,
        ?int $schoolId,
    ): LedgerEntry {
        return LedgerEntry::query()->create([
            'school_id' => $schoolId,
            'student_id' => $studentId,
            'source_type' => $source::class,
            'source_id' => $source->getKey(),
            'entry_type' => $type->value,
            'amount' => $amount,
            'narration' => $narration,
            'entry_date' => now()->toDateString(),
        ]);
    }
}
