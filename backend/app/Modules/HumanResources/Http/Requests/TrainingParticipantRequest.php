<?php

declare(strict_types=1);

namespace App\Modules\HumanResources\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

/** Enrol an employee on a training programme. */
class TrainingParticipantRequest extends BaseRequest
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
            'staff_id' => ['required', 'integer', 'exists:staff,id'],
        ];
    }
}
