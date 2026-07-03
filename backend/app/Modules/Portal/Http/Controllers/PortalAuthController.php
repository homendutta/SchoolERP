<?php

declare(strict_types=1);

namespace App\Modules\Portal\Http\Controllers;

use App\Modules\Authentication\Services\AuthService;
use App\Modules\Portal\Services\PortalContextService;
use App\Platform\Shared\Http\Controllers\BaseController;
use App\Platform\Shared\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * Portal authentication — reuses the existing Identity/Auth system (AuthService +
 * Sanctum). Login additionally resolves the caller's portal role so the client
 * can render the correct portal. No new auth logic is introduced.
 */
class PortalAuthController extends BaseController
{
    public function __construct(
        private readonly AuthService $auth,
        private readonly PortalContextService $context,
    ) {}

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = $this->auth->authenticate($validated['identifier'], $validated['password']);
        if ($user === null) {
            return ApiResponse::error('Invalid credentials.', 401, 'INVALID_CREDENTIALS');
        }

        // Only accounts linked to a portal profile (parent/student/teacher) may log in here.
        try {
            $context = $this->context->resolve($user);
        } catch (\Throwable) {
            return ApiResponse::error('This account has no portal access.', 403, 'NO_PORTAL_PROFILE');
        }

        $this->auth->recordLogin($user);
        $token = $user->createToken('portal')->plainTextToken;

        return $this->ok([
            'token' => $token,
            'token_type' => 'Bearer',
            'role' => $context->role->value,
            'user' => ['id' => $user->id, 'school_id' => $user->school_id],
        ], 'Logged in.');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return $this->ok(null, 'Logged out.');
    }

    public function me(Request $request): JsonResponse
    {
        $context = $this->context->resolve($request->user());

        return $this->ok([
            'role' => $context->role->value,
            'students' => $context->students->map(fn ($s) => [
                'id' => (int) $s->id, 'name' => $s->name ?? null, 'admission_number' => $s->admission_number ?? null,
            ])->values()->all(),
            'staff' => $context->staff !== null
                ? ['id' => (int) $context->staff->id, 'name' => $context->staff->name, 'employee_number' => $context->staff->employee_number]
                : null,
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        if (! Hash::check($validated['current_password'], (string) $user->password)) {
            return ApiResponse::error('Current password is incorrect.', 422, 'WRONG_PASSWORD');
        }

        $user->password = Hash::make($validated['password']);
        $user->must_change_password = false;
        $user->save();

        return $this->ok(null, 'Password changed.');
    }
}
