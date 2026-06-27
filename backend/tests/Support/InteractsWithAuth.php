<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Modules\Administration\Models\Permission;
use App\Modules\Administration\Models\Role;
use App\Modules\Administration\Models\User;
use Illuminate\Support\Str;

trait InteractsWithAuth
{
    protected string $defaultPassword = 'Password@123';

    /**
     * Create a user, optionally a super admin or granted a set of permission slugs.
     *
     * @param  array<int, string>  $permissionSlugs
     */
    protected function makeUser(
        array $permissionSlugs = [],
        bool $superAdmin = false,
        string $status = 'active'
    ): User {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user_'.Str::random(8).'@asylinx.test',
            'username' => 'user_'.Str::random(6),
            'password' => $this->defaultPassword, // hashed by the model's 'hashed' cast
            'status' => $status,
            'is_super_admin' => $superAdmin,
        ]);

        if ($permissionSlugs !== []) {
            $role = Role::create(['name' => 'Test Role', 'slug' => 'test_'.Str::random(8)]);
            $ids = [];
            foreach ($permissionSlugs as $slug) {
                $perm = Permission::firstOrCreate(
                    ['slug' => $slug],
                    ['name' => $slug, 'module' => 'Test', 'action' => 'view'],
                );
                $ids[] = $perm->id;
            }
            $role->permissions()->sync($ids);
            $user->roles()->attach($role->id);
        }

        return $user->fresh(['roles.permissions']);
    }
}
