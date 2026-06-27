<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Administration\Database\Seeders\FoundationSeeder;
use App\Modules\Administration\Database\Seeders\PermissionSeeder;
use App\Modules\Administration\Database\Seeders\RoleSeeder;
use Illuminate\Database\Seeder;

/**
 * Root database seeder. Delegates to module seeders (Sprint 1: Administration
 * foundation only). System bootstrap data only — no business data.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            FoundationSeeder::class,
        ]);
    }
}
