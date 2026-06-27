<?php

declare(strict_types=1);

namespace App\Modules\Administration\Database\Seeders;

use App\Modules\Administration\Models\Permission;
use Illuminate\Database\Seeder;

/**
 * Seeds the foundation permission catalogue (Sprint 1 scope). Module-specific
 * permissions are added by each module as it is built.
 */
class PermissionSeeder extends Seeder
{
    /** @var array<string, array<int, string>> module => actions */
    private const CATALOG = [
        'dashboard' => ['view'],
        'users' => ['view', 'create', 'edit', 'delete'],
        'roles' => ['view', 'create', 'edit', 'delete'],
        'permissions' => ['view'],
        // Administration (Sprint 2)
        'school' => ['view', 'update'],
        'settings' => ['view', 'update'],
        'master_data' => ['view', 'create', 'edit', 'delete'],
        'number_generator' => ['view', 'manage', 'reset'],
        'feature_flags' => ['view', 'manage'],
        'gateways' => ['view', 'manage', 'test'],
        'import' => ['execute'],
        'export' => ['execute'],
    ];

    public function run(): void
    {
        foreach (self::CATALOG as $module => $actions) {
            foreach ($actions as $action) {
                $slug = "{$module}.{$action}";
                Permission::query()->updateOrCreate(
                    ['slug' => $slug],
                    [
                        'name' => ucfirst($action).' '.str_replace('_', ' ', $module),
                        'module' => ucfirst($module),
                        'action' => $action,
                    ],
                );
            }
        }
    }
}
