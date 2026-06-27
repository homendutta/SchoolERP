<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Requests;

use App\Modules\Academic\Enums\CalendarEventType;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class CalendarEventRequest extends BaseRequest
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
        $required = $this->isMethod('post') ? 'required' : 'sometimes';

        return [
            'academic_calendar_id' => [$required, 'integer', 'exists:academic_calendars,id'],
            'holiday_type_id' => ['nullable', 'integer', 'exists:holiday_types,id'],
            'title' => [$required, 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'event_type' => [$required, Rule::enum(CalendarEventType::class)],
            'start_date' => [$required, 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'is_recurring' => ['sometimes', 'boolean'],
        ];
    }
}
