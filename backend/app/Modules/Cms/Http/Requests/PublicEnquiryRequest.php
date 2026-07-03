<?php

declare(strict_types=1);

namespace App\Modules\Cms\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;

/** Public admission enquiry (unauthenticated, throttled). Enquiry only — no admission is created. */
class PublicEnquiryRequest extends BaseRequest
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
            'school_id' => ['required', 'integer', 'exists:schools,id'],
            'parent_name' => ['required', 'string', 'max:255'],
            'student_name' => ['nullable', 'string', 'max:255'],
            'interested_class' => ['nullable', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
