<?php

declare(strict_types=1);

namespace App\Modules\Transport\Http\Requests;

use App\Modules\Transport\Enums\TripShift;
use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class AssignStudentRequest extends BaseRequest
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
            'route_id' => ['required', 'integer', 'exists:transport_routes,id'],
            'stop_id' => ['required', 'integer', 'exists:transport_stops,id'],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'shift' => ['nullable', Rule::in(TripShift::values())],
        ];
    }
}
