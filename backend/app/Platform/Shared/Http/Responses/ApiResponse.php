<?php

declare(strict_types=1);

namespace App\Platform\Shared\Http\Responses;

use Illuminate\Http\JsonResponse;

/**
 * Standard API response envelope.
 *
 * The single, consistent response shape consumed identically by the React web
 * client and the Flutter app (one API for both). Conventions follow the
 * approved API Architecture (docs/03-system-architecture/06-api-architecture.md).
 *
 * This is transport scaffolding only — it contains no business logic and
 * defines no endpoints.
 */
final class ApiResponse
{
    /**
     * A successful response.
     *
     * @param  array<string, mixed>  $meta
     */
    public static function success(
        mixed $data = null,
        ?string $message = null,
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        return response()->json(array_filter([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta ?: null,
        ], static fn ($value) => $value !== null), $status);
    }

    /**
     * An error response.
     *
     * @param  array<string, mixed>  $errors
     */
    public static function error(
        string $message,
        int $status = 400,
        ?string $code = null,
        array $errors = []
    ): JsonResponse {
        return response()->json(array_filter([
            'success' => false,
            'message' => $message,
            'code' => $code,
            'errors' => $errors ?: null,
        ], static fn ($value) => $value !== null), $status);
    }
}
