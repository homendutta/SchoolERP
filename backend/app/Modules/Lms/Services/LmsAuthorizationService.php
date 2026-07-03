<?php

declare(strict_types=1);

namespace App\Modules\Lms\Services;

use App\Modules\Academic\Models\TeacherSubjectAssignment;
use App\Modules\Administration\Models\User;
use App\Modules\Portal\Services\PortalContextService;
use App\Modules\Students\Models\Student;
use App\Platform\Shared\Exceptions\DomainException;

/**
 * The LMS authorization boundary. It owns no data logic — it reuses the Academic
 * teacher-subject assignments (teachers manage only their assigned subjects) and
 * the Portal context (students see only their own data, parents only their linked
 * children). Super admins bypass, mirroring the platform RBAC convention.
 */
class LmsAuthorizationService
{
    public function __construct(private readonly PortalContextService $portal) {}

    private function isSuperAdmin(User $user): bool
    {
        return (bool) ($user->is_super_admin ?? false);
    }

    /** Ensure the user teaches the given subject (optionally for a class). */
    public function authorizeTeacherSubject(User $user, int $subjectId, ?int $classId = null, ?int $sectionId = null): void
    {
        if ($this->isSuperAdmin($user)) {
            return;
        }

        $exists = TeacherSubjectAssignment::query()
            ->where('teacher_id', $user->id)
            ->where('subject_id', $subjectId)
            ->when($classId !== null, fn ($q) => $q->where('class_id', $classId))
            ->when($sectionId !== null, fn ($q) => $q->where(fn ($w) => $w->whereNull('section_id')->orWhere('section_id', $sectionId)))
            ->exists();

        if (! $exists) {
            throw new DomainException('You are not assigned to teach this subject.', 403, 'SUBJECT_FORBIDDEN');
        }
    }

    /** True when the user teaches at least one subject (or is a super admin). */
    public function isTeacher(User $user): bool
    {
        return $this->isSuperAdmin($user)
            || TeacherSubjectAssignment::query()->where('teacher_id', $user->id)->exists();
    }

    public function requireTeacher(User $user): void
    {
        if (! $this->isTeacher($user)) {
            throw new DomainException('This action is available to teachers only.', 403, 'TEACHER_ONLY');
        }
    }

    /** The student ids the user may access (child(ren) for a parent, self for a student). */
    public function authorizedStudentIds(User $user): array
    {
        return $this->portal->authorizedStudentIds($user);
    }

    /** Ensure the user may act for the given student (parent → child, student → self). */
    public function authorizeStudent(User $user, int $studentId): Student
    {
        return $this->portal->authorizeStudent($user, $studentId);
    }
}
