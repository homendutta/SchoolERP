<?php

declare(strict_types=1);

namespace App\Platform\Shared\Http\Requests;

use App\Platform\Shared\Http\Responses\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Base form request for every module.
 *
 * Requests own BOTH input validation and authorization (action grant + data
 * scope) for an action. Services trust the validated, authorized payload.
 *
 * Concrete module requests implement rules() and authorize(); on failure this
 * base returns the standard API error envelope so web and mobile receive a
 * consistent shape.
 */
abstract class BaseRequest extends FormRequest
{
    /**
     * Default deny — concrete requests must explicitly grant via the permission
     * model (RBAC action grant + data scope). Enforcement is always server-side.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Validation rules. Concrete requests override.
     *
     * @return array<string, mixed>
     */
    abstract public function rules(): array;

    /**
     * Return validation failures in the standard envelope.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                message: 'The given data was invalid.',
                status: 422,
                code: 'VALIDATION_ERROR',
                errors: $validator->errors()->toArray()
            )
        );
    }

    /**
     * Return authorization failures in the standard envelope.
     */
    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            ApiResponse::error(
                message: 'This action is unauthorized.',
                status: 403,
                code: 'FORBIDDEN'
            )
        );
    }
}
