<?php

declare(strict_types=1);

namespace App\Platform\Shared\Http\Controllers;

use App\Platform\Shared\Http\Responses\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as LaravelController;

/**
 * Base controller for every module.
 *
 * Controllers are THIN orchestrators: they receive a validated Request, invoke
 * ONE Service method, and return a Resource/envelope. They contain no business
 * rules, no validation logic, and no direct data access — those belong to
 * Requests, Services, and Repositories respectively
 * (docs/03-system-architecture/03-backend-architecture.md).
 */
abstract class BaseController extends LaravelController
{
    use AuthorizesRequests;
    use ValidatesRequests;

    /**
     * Convenience helper for the standard success envelope.
     *
     * @param  array<string, mixed>  $meta
     */
    protected function ok(mixed $data = null, ?string $message = null, int $status = 200, array $meta = [])
    {
        return ApiResponse::success($data, $message, $status, $meta);
    }

    /**
     * Convenience helper for the standard error envelope.
     *
     * @param  array<string, mixed>  $errors
     */
    protected function fail(string $message, int $status = 400, ?string $code = null, array $errors = [])
    {
        return ApiResponse::error($message, $status, $code, $errors);
    }
}
