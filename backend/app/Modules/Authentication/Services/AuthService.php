<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Services;

use App\Modules\Administration\Models\User;
use App\Platform\Shared\Services\BaseService;
use Illuminate\Support\Facades\Hash;

/**
 * Authentication service.
 *
 * Resolves a unified login identifier (email / username / staff number) to a
 * user, applies the login gates (active status, not deleted), and verifies the
 * password. Token issuing is performed by the controller (request-bound).
 */
class AuthService extends BaseService
{
    /**
     * @return User|null the authenticated user, or null on any failure
     */
    public function authenticate(string $identifier, string $password): ?User
    {
        $identifier = trim($identifier);

        $user = User::query()
            ->where('email', $identifier)
            ->orWhere('username', $identifier)
            ->orWhere('staff_number', $identifier)
            ->first();

        if ($user === null || ! $user->isActive()) {
            return null;
        }

        if (! Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    public function recordLogin(User $user): void
    {
        $user->forceFill(['last_login_at' => now()])->saveQuietly();
    }
}
