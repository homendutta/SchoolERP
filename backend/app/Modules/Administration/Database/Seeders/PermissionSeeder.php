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
        // Platform — shared Media Upload Pipeline (used by every module)
        'media' => ['view', 'upload', 'delete'],
        // Platform — Identity Service (permanent person-identity + QR/barcode)
        'identity' => ['view', 'manage'],
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
        // Academic (Sprint 3)
        'academic.years' => ['view', 'create', 'edit', 'delete'],
        'academic.terms' => ['view', 'create', 'edit', 'delete'],
        'academic.calendar' => ['view', 'create', 'edit', 'delete'],
        'academic.classes' => ['view', 'create', 'edit', 'delete'],
        'academic.sections' => ['view', 'create', 'edit', 'delete'],
        'academic.rooms' => ['view', 'create', 'edit', 'delete'],
        'academic.subjects' => ['view', 'create', 'edit', 'delete'],
        'academic.subject_groups' => ['view', 'create', 'edit', 'delete'],
        'academic.teacher_assignments' => ['view', 'create', 'edit', 'delete'],
        'academic.class_teachers' => ['view', 'assign'],
        // Admissions (Sprint 4)
        'admissions.dashboard' => ['view'],
        'admissions.enquiries' => ['view', 'create', 'edit', 'delete'],
        'admissions.applications' => ['view', 'create', 'edit', 'delete'],
        'admissions.documents' => ['view', 'create', 'edit', 'delete'],
        'admissions.verification' => ['view', 'manage'],
        'admissions.approval' => ['view', 'create', 'edit', 'delete', 'manage'],
        'admissions.enroll' => ['execute'],
        'admissions.import' => ['execute'],
        // Students (Sprint 5)
        'students' => ['view', 'edit', 'promote', 'transfer', 'withdraw', 'import', 'export'],
        // Staff (Sprint 6)
        'staff' => ['view', 'create', 'edit', 'delete', 'import', 'export'],
        // Attendance (Sprint 7)
        'attendance' => ['view', 'mark', 'correct', 'import', 'devices', 'biometric'],
        // Timetable (Sprint 8)
        'timetable' => ['view', 'manage', 'substitute', 'copy'],
        // Examination (Sprint 9)
        'examinations' => ['view', 'manage', 'marks', 'publish'],
        // Finance & Fees (Sprint 10)
        'finance' => ['view', 'manage', 'collect', 'refund'],
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
