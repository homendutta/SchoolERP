<?php

declare(strict_types=1);

namespace App\Platform\Core\Middleware;

use App\Platform\Shared\Http\Responses\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Permission middleware (framework infrastructure).
 *
 * Server-side RBAC enforcement: allows the request only when the authenticated
 * user holds the required permission slug. Super admins bypass the check (handled
 * inside the user's hasPermission()). Usage: ->middleware('permission:users.view').
 */
class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if ($user === null) {
            return ApiResponse::error('Unauthenticated.', 401, 'UNAUTHENTICATED');
        }

        if (! method_exists($user, 'hasPermission') || ! $user->hasPermission($permission)) {
            return ApiResponse::error('This action is unauthorized.', 403, 'FORBIDDEN');
        }

        return $next($request);
    }
}
