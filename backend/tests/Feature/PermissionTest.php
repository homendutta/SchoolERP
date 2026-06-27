<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\Support\InteractsWithAuth;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use InteractsWithAuth;
    use RefreshDatabase;

    public function test_unauthenticated_request_is_blocked(): void
    {
        $this->getJson('/api/v1/users')->assertStatus(401);
    }

    public function test_user_without_permission_is_forbidden(): void
    {
        Sanctum::actingAs($this->makeUser());

        $this->getJson('/api/v1/users')
            ->assertStatus(403)
            ->assertJsonPath('code', 'FORBIDDEN');
    }

    public function test_user_with_permission_is_allowed(): void
    {
        Sanctum::actingAs($this->makeUser(['users.view']));

        $this->getJson('/api/v1/users')->assertOk()->assertJsonPath('success', true);
    }

    public function test_super_admin_bypasses_permission_checks(): void
    {
        Sanctum::actingAs($this->makeUser(superAdmin: true));

        $this->getJson('/api/v1/users')->assertOk();
    }
}
