<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\InteractsWithAuth;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use InteractsWithAuth;
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = $this->makeUser();

        $response = $this->postJson('/api/v1/auth/login', [
            'identifier' => $user->email,
            'password' => $this->defaultPassword,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => ['token', 'token_type', 'user' => ['id', 'email', 'roles', 'permissions']],
            ]);

        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_login_can_use_username_identifier(): void
    {
        $user = $this->makeUser();

        $this->postJson('/api/v1/auth/login', [
            'identifier' => $user->username,
            'password' => $this->defaultPassword,
        ])->assertOk();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = $this->makeUser();

        $this->postJson('/api/v1/auth/login', [
            'identifier' => $user->email,
            'password' => 'incorrect',
        ])->assertStatus(401)->assertJsonPath('success', false);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = $this->makeUser(status: 'suspended');

        $this->postJson('/api/v1/auth/login', [
            'identifier' => $user->email,
            'password' => $this->defaultPassword,
        ])->assertStatus(401);
    }

    public function test_login_validation_requires_identifier_and_password(): void
    {
        $this->postJson('/api/v1/auth/login', [])
            ->assertStatus(422)
            ->assertJsonPath('code', 'VALIDATION_ERROR');
    }

    public function test_me_returns_the_authenticated_user(): void
    {
        $user = $this->makeUser();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_me_requires_authentication(): void
    {
        $this->getJson('/api/v1/auth/me')->assertStatus(401);
    }

    public function test_user_can_logout_and_token_is_revoked(): void
    {
        $user = $this->makeUser();

        $token = $this->postJson('/api/v1/auth/login', [
            'identifier' => $user->email,
            'password' => $this->defaultPassword,
        ])->json('data.token');

        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();

        // The token is revoked (deleted) — it can no longer authenticate.
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
