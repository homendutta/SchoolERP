<?php

declare(strict_types=1);

namespace App\Modules\Timetable\Http\Requests;

use App\Modules\Timetable\Enums\Weekday;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class WorkingDaysRequest extends BaseRequest
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
            'days' => ['required', 'array', 'min:1'],
            'days.*.weekday' => ['required', Rule::in(Weekday::values())],
            'days.*.is_working' => ['sometimes', 'boolean'],
        ];
    }
}
