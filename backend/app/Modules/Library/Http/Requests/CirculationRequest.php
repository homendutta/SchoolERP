<?php

declare(strict_types=1);

namespace App\Modules\Library\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

/**
 * Validation for all circulation actions (borrow / return / renew / reserve).
 * A single request keyed by the route's action keeps the rules co-located; the
 * borrower is always addressed by Identity Number (never a raw Student/Staff id).
 */
class CirculationRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return match ($this->route()?->getActionMethod()) {
            'borrow' => [
                'school_id' => ['required', 'integer', 'exists:schools,id'],
                'identity_number' => ['required', 'string'],
                'copy_id' => ['required', 'integer', 'exists:library_copies,id'],
            ],
            'reserve' => [
                'school_id' => ['required', 'integer', 'exists:schools,id'],
                'identity_number' => ['required', 'string'],
                'book_id' => ['required', 'integer', 'exists:library_books,id'],
            ],
            'returnCopy' => [
                'borrowing_id' => ['required', 'integer', 'exists:library_borrowings,id'],
                'return_date' => ['nullable', 'date'],
                'damage_notes' => ['nullable', 'string', 'max:1000'],
            ],
            'renew' => [
                'borrowing_id' => ['required', 'integer', 'exists:library_borrowings,id'],
            ],
            default => [],
        };
    }
}
