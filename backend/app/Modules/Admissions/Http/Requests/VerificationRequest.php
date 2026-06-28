<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Http\Requests;

use App\Modules\Admissions\Enums\VerificationStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class VerificationRequest extends BaseRequest
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
            'status' => ['required', Rule::enum(VerificationStatus::class)],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
