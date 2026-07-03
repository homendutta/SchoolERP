<?php

declare(strict_types=1);

namespace App\Modules\Portal\Services;

use App\Modules\Administration\Models\User;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Portal\Enums\PortalRole;
use App\Modules\Staff\Models\Staff;
use App\Modules\Students\Models\Student;
use App\Platform\Shared\Exceptions\DomainException;
use Illuminate\Support\Facades\DB;

/**
 * Resolves the authenticated user into a portal context and ENFORCES isolation:
 *   - a Parent (Guardian) may access only their linked children,
 *   - a Student may access only their own record,
 *   - a Teacher (Staff) may access only their assigned responsibilities.
 *
 * This is the single authorization boundary for every portal read/write — the
 * portal owns no other business logic.
 */
class PortalContextService
{
    /** Resolve the portal role + the linked person(s) for a user. */
    public function resolve(User $user): PortalContext
    {
        $guardian = Guardian::query()->where('user_id', $user->id)->first();
        if ($guardian !== null) {
            // Resolve linked children via the pivot directly (avoids coupling to the
            // Guardian relation's pivot-column declarations).
            $studentIds = DB::table('student_guardian')->where('guardian_id', $guardian->id)->pluck('student_id');
            $students = Student::query()->whereIn('id', $studentIds)->get();

            return new PortalContext(PortalRole::Parent, $user, $students, $guardian, null);
        }

        $student = Student::query()->where('user_id', $user->id)->first();
        if ($student !== null) {
            return new PortalContext(PortalRole::Student, $user, collect([$student]), null, null);
        }

        $staff = Staff::query()->where('user_id', $user->id)->first();
        if ($staff !== null) {
            return new PortalContext(PortalRole::Teacher, $user, collect(), null, $staff);
        }

        throw new DomainException('No portal profile is linked to this account.', 403, 'NO_PORTAL_PROFILE');
    }

    /**
     * Return the student ids the user is authorized to see (children / self).
     *
     * @return array<int, int>
     */
    public function authorizedStudentIds(User $user): array
    {
        return $this->resolve($user)->students->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * Ensure the user may access the given student; returns the Student or 403s.
     */
    public function authorizeStudent(User $user, int $studentId): Student
    {
        $context = $this->resolve($user);
        $student = $context->students->firstWhere('id', $studentId);
        if ($student === null) {
            throw new DomainException('You are not authorized to access this student.', 403, 'STUDENT_FORBIDDEN');
        }

        return $student;
    }

    /** Ensure the user is a teacher; returns the Staff or 403s. */
    public function requireTeacher(User $user): Staff
    {
        $context = $this->resolve($user);
        if ($context->role !== PortalRole::Teacher || $context->staff === null) {
            throw new DomainException('This action is available to teachers only.', 403, 'TEACHER_ONLY');
        }

        return $context->staff;
    }

    /** Ensure the user may pay fees (parents + students only; teachers cannot). */
    public function requireFeePayer(User $user): PortalContext
    {
        $context = $this->resolve($user);
        if ($context->role === PortalRole::Teacher) {
            throw new DomainException('Teachers do not have fee access.', 403, 'FEES_FORBIDDEN');
        }

        return $context;
    }
}
