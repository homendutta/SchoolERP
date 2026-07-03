<?php

declare(strict_types=1);

use App\Modules\Administration\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature/Administration');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Academic');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Admissions');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Media');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Students');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Staff');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Identity');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Attendance');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Timetable');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Examination');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Finance');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Communication');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Library');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Transport');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Hostel');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Inventory');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Hr');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Payroll');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Cms');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Portal');
uses(TestCase::class, RefreshDatabase::class)->in('Feature/Lms');

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
