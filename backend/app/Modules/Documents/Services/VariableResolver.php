<?php

declare(strict_types=1);

namespace App\Modules\Documents\Services;

use App\Modules\Administration\Models\School;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Staff\Models\Staff;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentAcademicRecord;
use App\Platform\Foundation\Identity\Models\Identity;

/**
 * The reusable merge engine. It resolves {{namespace.field}} variables from the
 * OWNING modules (Student / Staff / Guardian / Academic / School …) — data is
 * never duplicated here, only read and projected — then substitutes them into a
 * template's HTML. Unknown variables are left blank.
 */
class VariableResolver
{
    /**
     * Build the variable map for a subject.
     *
     * @return array<string, string>
     */
    public function resolve(int $schoolId, string $subjectKind, ?int $subjectId, ?Identity $identity = null): array
    {
        $vars = $this->schoolVars($schoolId);
        $vars['date.today'] = now()->toDateString();
        if ($identity !== null) {
            $vars['document.verification_code'] = (string) $identity->identity_number;
            $vars['document.verification_id'] = (string) $identity->public_identifier;
        }

        if ($subjectId === null) {
            return $vars;
        }

        return match ($subjectKind) {
            'staff' => array_merge($vars, $this->staffVars($subjectId)),
            'guardian' => array_merge($vars, $this->guardianVars($subjectId)),
            default => array_merge($vars, $this->studentVars($subjectId)),
        };
    }

    /**
     * Substitute {{ key }} placeholders in the HTML with resolved values.
     *
     * @param  array<string, string>  $vars
     */
    public function merge(string $html, array $vars): string
    {
        return (string) preg_replace_callback('/\{\{\s*([a-zA-Z0-9_.]+)\s*\}\}/', function (array $m) use ($vars): string {
            return $vars[$m[1]] ?? '';
        }, $html);
    }

    /** @return array<string, string> */
    private function schoolVars(int $schoolId): array
    {
        $school = School::query()->find($schoolId);

        return [
            'school.name' => (string) ($school?->name ?? ''),
            'school.code' => (string) ($school?->code ?? ''),
        ];
    }

    /** @return array<string, string> */
    private function studentVars(int $studentId): array
    {
        $student = Student::query()->find($studentId);
        if ($student === null) {
            return [];
        }
        $record = StudentAcademicRecord::query()->where('student_id', $studentId)
            ->where('is_current', true)->with(['schoolClass:id,name', 'section:id,name'])->latest('id')->first();

        return [
            'student.name' => (string) $student->name,
            'student.admission_no' => (string) $student->admission_number,
            'student.email' => (string) ($student->email ?? ''),
            'class.name' => (string) ($record?->getRelation('schoolClass')?->name ?? ''),
            'section.name' => (string) ($record?->getRelation('section')?->name ?? ''),
            'roll.number' => (string) ($record?->roll_number ?? ''),
        ];
    }

    /** @return array<string, string> */
    private function staffVars(int $staffId): array
    {
        $staff = Staff::query()->find($staffId);
        if ($staff === null) {
            return [];
        }

        return [
            'staff.name' => (string) $staff->name,
            'staff.employee_no' => (string) $staff->employee_number,
            'staff.email' => (string) ($staff->email ?? ''),
        ];
    }

    /** @return array<string, string> */
    private function guardianVars(int $guardianId): array
    {
        $guardian = Guardian::query()->find($guardianId);
        if ($guardian === null) {
            return [];
        }

        return [
            'guardian.name' => (string) $guardian->name,
            'guardian.phone' => (string) ($guardian->phone ?? ''),
        ];
    }
}
