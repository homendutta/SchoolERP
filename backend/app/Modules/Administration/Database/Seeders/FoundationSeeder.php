<?php

declare(strict_types=1);

namespace App\Modules\Administration\Database\Seeders;

use App\Modules\Administration\Enums\FeatureFlagKey;
use App\Modules\Administration\Models\FeatureFlag;
use App\Modules\Administration\Models\MasterDataGroup;
use App\Modules\Administration\Models\MasterDataType;
use App\Modules\Administration\Models\NumberSequence;
use App\Modules\Administration\Models\Role;
use App\Modules\Administration\Models\School;
use App\Modules\Administration\Models\Setting;
use App\Modules\Administration\Models\User;
use Illuminate\Database\Seeder;

/**
 * Bootstraps a usable installation: one school (+ focused settings), the super
 * admin + a sample administrator, baseline settings, master data, number
 * sequences, and feature flags. System bootstrap data only.
 */
class FoundationSeeder extends Seeder
{
    public function run(): void
    {
        $school = School::query()->updateOrCreate(
            ['code' => 'ASYLINX'],
            ['name' => 'Asylinx Demo School', 'short_name' => 'Asylinx', 'is_active' => true],
        );

        $school->contact()->updateOrCreate([], [
            'email' => 'office@asylinx.test', 'phone' => '+910000000000', 'website' => 'https://asylinx.test',
        ]);
        $school->regional()->updateOrCreate([], [
            'timezone' => 'UTC', 'currency' => 'INR', 'locale' => 'en',
        ]);
        $school->academic()->updateOrCreate([], [
            'academic_year' => '2025-2026', 'academic_year_start_month' => 4,
        ]);
        $school->branding()->updateOrCreate([], ['theme_color' => '#001F3F']);

        $superAdminRole = Role::query()->where('slug', 'super_admin')->first();
        $adminRole = Role::query()->where('slug', 'administrator')->first();

        $superAdmin = User::query()->updateOrCreate(
            ['email' => 'superadmin@asylinx.test'],
            [
                'school_id' => $school->id, 'name' => 'Super Admin', 'username' => 'superadmin',
                'password' => 'Password@123', 'status' => 'active', 'is_super_admin' => true,
            ],
        );
        $superAdminRole && $superAdmin->roles()->syncWithoutDetaching([$superAdminRole->id]);

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@asylinx.test'],
            [
                'school_id' => $school->id, 'name' => 'School Administrator', 'username' => 'admin',
                'password' => 'Password@123', 'status' => 'active', 'is_super_admin' => false,
            ],
        );
        $adminRole && $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        foreach ([
            ['general', 'school_name', 'Asylinx Demo School', 'string'],
            ['general', 'academic_year', '2025-2026', 'string'],
            ['security', 'session_lifetime_minutes', '120', 'int'],
            ['appearance', 'theme_color', '#001F3F', 'string'],
        ] as [$group, $key, $value, $type]) {
            Setting::query()->updateOrCreate(
                ['school_id' => $school->id, 'group' => $group, 'key' => $key],
                ['value' => $value, 'type' => $type],
            );
        }

        // Master data: Group -> Type -> Values
        $hr = MasterDataGroup::query()->updateOrCreate(
            ['slug' => 'general'],
            ['name' => 'General', 'is_system' => true],
        );
        $blood = MasterDataType::query()->updateOrCreate(
            ['slug' => 'blood_group'],
            ['group_id' => $hr->id, 'name' => 'Blood Group', 'is_system' => true],
        );
        foreach (['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $i => $bg) {
            $blood->values()->updateOrCreate(['value' => $bg], ['label' => $bg, 'sort_order' => $i]);
        }

        // Number sequences
        NumberSequence::query()->updateOrCreate(
            ['school_id' => $school->id, 'key' => 'admission_number'],
            ['label' => 'Admission Number', 'initial_number' => 100001, 'padding' => 6, 'format' => '{number}'],
        );
        NumberSequence::query()->updateOrCreate(
            ['school_id' => $school->id, 'key' => 'receipt_number'],
            ['label' => 'Receipt Number', 'prefix' => 'RCP-', 'padding' => 8, 'format' => '{prefix}{year}-{number}', 'reset_policy' => 'yearly'],
        );

        // Feature flags (disabled by default)
        foreach (FeatureFlagKey::cases() as $flag) {
            FeatureFlag::query()->updateOrCreate(
                ['key' => $flag->value],
                ['label' => $flag->label(), 'is_enabled' => false],
            );
        }
    }
}
