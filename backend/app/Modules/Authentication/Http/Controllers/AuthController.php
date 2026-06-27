<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Http\Controllers;

use App\Modules\Administration\Http\Resources\UserResource;
use App\Modules\Authentication\Http\Requests\LoginRequest;
use App\Modules\Authentication\Services\AuthService;
use App\Platform\Shared\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Authentication endpoints (Sanctum token-based, consumed by web + mobile):
 *   POST /api/v1/auth/login   POST /api/v1/auth/logout   GET /api/v1/auth/me
 *
 * Thin orchestration only — the AuthService holds the rules.
 */
class AuthController extends BaseController
{
    public function __construct(private readonly AuthService $auth) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $user = $this->auth->authenticate(
            (string) $request->string('identifier'),
            (string) $request->string('password'),
        );

        if ($user === null) {
            return $this->fail('Invalid credentials.', 401, 'INVALID_CREDENTIALS');
        }

        $this->auth->recordLogin($user);
        $token = $user->createToken('api')->plainTextToken;
        $user->load('roles.permissions');

        return $this->ok([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 'Logged in successfully.');
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('roles.permissions');

        return $this->ok(new UserResource($user));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->ok(null, 'Logged out successfully.');
    }
}
