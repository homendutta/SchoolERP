<?php

declare(strict_types=1);

namespace App\Modules\Finance\Models;

use App\Modules\Finance\Enums\LedgerEntryType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/** A financial ledger entry — the accounting impact, independent of payments. */
class LedgerEntry extends Model
{
    protected $table = 'ledger_entries';

    protected $fillable = [
        'school_id', 'student_id', 'source_type', 'source_id',
        'entry_type', 'amount', 'narration', 'entry_date',
    ];

    protected function casts(): array
    {
        return ['amount' => 'float', 'entry_date' => 'date', 'entry_type' => LedgerEntryType::class];
    }

    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
