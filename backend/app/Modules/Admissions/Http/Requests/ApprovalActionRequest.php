<?php

declare(strict_types=1);

namespace App\Modules\Admissions\Http\Requests;

use App\Modules\Admissions\Enums\ApprovalStepStatus;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class ApprovalActionRequest extends BaseRequest
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
            'decision' => ['required', Rule::enum(ApprovalStepStatus::class)],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
