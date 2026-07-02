<?php

declare(strict_types=1);

namespace App\Modules\Hostel\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

class TransferRequest extends BaseRequest
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
        return [
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'to_bed_id' => ['required', 'integer', 'exists:hostel_beds,id'],
            'transfer_type' => ['nullable', 'string', 'in:room,bed,building,hostel'],
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
