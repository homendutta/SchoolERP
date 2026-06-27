<?php

declare(strict_types=1);

namespace App\Modules\Academic\Http\Requests;

use App\Platform\Shared\Http\Requests\BaseRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubjectRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->filled('slug') && $this->filled('name')) {
            $this->merge(['slug' => Str::slug((string) $this->input('name'))]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $required = $this->isMethod('post') ? 'required' : 'sometimes';
        $id = $this->route('id');

        return [
            'school_id' => [$required, 'integer', 'exists:schools,id'],
            'subject_type_id' => ['nullable', 'integer', 'exists:master_data_values,id'],
            'code' => [$required, 'string', 'max:50', Rule::unique('subjects', 'code')->ignore($id)->whereNull('deleted_at')],
            'name' => [$required, 'string', 'max:255'],
            'short_name' => ['nullable', 'string', 'max:50'],
            'slug' => ['nullable', 'string', 'max:255'],
            'theory' => ['sometimes', 'boolean'],
            'practical' => ['sometimes', 'boolean'],
            'credits' => ['nullable', 'integer', 'min:0'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
