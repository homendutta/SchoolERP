<?php

declare(strict_types=1);

namespace App\Modules\Administration\Database\Seeders;

use App\Modules\Administration\Models\Permission;
use App\Modules\Administration\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Seeds the default (system) roles and their Sprint-1 permission grants.
 */
class RoleSeeder extends Seeder
{
    /** @var array<int, array{slug: string, name: string}> */
    private const ROLES = [
        ['slug' => 'super_admin', 'name' => 'Super Admin'],
        ['slug' => 'administrator', 'name' => 'Administrator'],
        ['slug' => 'supervisor', 'name' => 'Supervisor'],
        ['slug' => 'accountant', 'name' => 'Accountant'],
        ['slug' => 'clerk', 'name' => 'Clerk'],
        ['slug' => 'receptionist', 'name' => 'Receptionist'],
        ['slug' => 'teacher', 'name' => 'Teacher'],
        ['slug' => 'student', 'name' => 'Student'],
        ['slug' => 'parent', 'name' => 'Parent'],
    ];

    public function run(): void
    {
        $allPermissionIds = Permission::query()->pluck('id')->all();
        $dashboardId = Permission::query()->where('slug', 'dashboard.view')->value('id');

        foreach (self::ROLES as $def) {
            $role = Role::query()->updateOrCreate(
                ['slug' => $def['slug']],
                ['name' => $def['name'], 'is_system' => true],
            );

            // super_admin & administrator get the full foundation grant; others
            // get dashboard only for Sprint 1 (deepened as their modules ship).
            $grant = in_array($def['slug'], ['super_admin', 'administrator'], true)
                ? $allPermissionIds
                : array_filter([$dashboardId]);

            $role->permissions()->sync($grant);
        }
    }
}
