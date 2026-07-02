<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Resources;

use App\Modules\Library\Models\Borrowing;
use App\Platform\Shared\Http\Resources\BaseResource;
use Illuminate\Http\Request;

/**
 * @mixin Borrowing
 */
class BorrowingResource extends BaseResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'identity_id' => $this->identity_id,
            'identity_number' => $this->whenLoaded('identity', fn () => $this->identity?->identity_number),
            'borrower' => $this->whenLoaded('owner', fn () => $this->owner?->getAttribute('name')),
            'owner_type' => class_basename((string) $this->owner_type),
            'copy_id' => $this->copy_id,
            'copy_number' => $this->whenLoaded('copy', fn () => $this->copy?->copy_number),
            'book_id' => $this->book_id,
            'book' => $this->whenLoaded('book', fn () => $this->book?->title),
            'borrow_date' => $this->borrow_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'return_date' => $this->return_date?->toDateString(),
            'status' => $this->status->value,
            'renewals_count' => $this->renewals_count,
            'late_days' => $this->late_days,
            'fine_amount' => $this->fine_amount,
            'fine_waived' => $this->fine_waived,
            'damage_notes' => $this->damage_notes,
        ];
    }
}
