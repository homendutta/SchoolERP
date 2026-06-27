<?php

declare(strict_types=1);

use App\Modules\Administration\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature/Administration');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Academic');

/** Create a super admin (bypasses permission checks) and authenticate as them. */
function actingAsSuperAdmin(): User
{
    $user = User::create([
        'name' => 'Sprint2 Admin',
        'email' => 'sprint2@asylinx.test',
        'username' => 'sprint2',
        'password' => 'Password@123',
        'status' => 'active',
        'is_super_admin' => true,
    ]);

    Sanctum::actingAs($user);

    return $user;
}
