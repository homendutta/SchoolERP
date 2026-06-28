<?php

declare(strict_types=1);

namespace App\Modules\Finance\Http\Resources;

use App\Modules\Finance\Models\LedgerEntry;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin LedgerEntry
 */
class LedgerEntryResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'student_id' => $this->student_id,
            'source_type' => class_basename((string) $this->source_type),
            'source_id' => $this->source_id,
            'entry_type' => $this->entry_type->value,
            'amount' => $this->amount,
            'narration' => $this->narration,
            'entry_date' => $this->entry_date?->toDateString(),
        ];
    }
}
