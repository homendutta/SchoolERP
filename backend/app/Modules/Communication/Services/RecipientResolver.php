<?php

declare(strict_types=1);

namespace App\Modules\Communication\Services;

use App\Modules\Communication\DTO\CommunicationRequestData;
use App\Modules\Communication\Enums\AudienceType;
use App\Modules\Parents\Models\Guardian;
use App\Modules\Staff\Models\Staff;
use App\Modules\Students\Models\Student;
use App\Modules\Students\Models\StudentAcademicRecord;

/**
 * Resolves an audience definition into concrete recipients. This responsibility
 * belongs to the engine — callers only name an audience. Each recipient carries
 * the address fields a channel may need (email / phone) plus an optional user_id
 * so per-user preferences can be applied.
 */
class RecipientResolver
{
    /**
     * @return array<int, array{recipient_type:string, recipient_id:int, recipient_name:string, email:?string, phone:?string, user_id:?int}>
     */
    public function resolve(CommunicationRequestData $request): array
    {
        return match ($request->audienceType) {
            AudienceType::Custom => $this->custom($request),
            AudienceType::Guardians => $this->guardians($request),
            AudienceType::Staff, AudienceType::Teachers, AudienceType::Department, AudienceType::Administrators => $this->staff($request),
            default => $this->students($request), // School / Class / Section / Students
        };
    }

    /**
     * @return array<int, array{recipient_type:string, recipient_id:int, recipient_name:string, email:?string, phone:?string, user_id:?int}>
     */
    private function students(CommunicationRequestData $request): array
    {
        $studentIds = StudentAcademicRecord::query()
            ->where('is_current', true)
            ->when($request->classId, fn ($q, $c) => $q->where('class_id', $c))
            ->when($request->sectionId, fn ($q, $s) => $q->where('section_id', $s))
            ->pluck('student_id');

        $students = Student::query()
            ->where('school_id', $request->schoolId)
            ->when($request->classId || $request->sectionId, fn ($q) => $q->whereIn('id', $studentIds))
            ->get(['id', 'name', 'email', 'phone', 'user_id']);

        return $students->map(fn (Student $s) => [
            'recipient_type' => Student::class,
            'recipient_id' => (int) $s->id,
            'recipient_name' => (string) $s->name,
            'email' => $s->email,
            'phone' => $s->phone,
            'user_id' => $s->user_id !== null ? (int) $s->user_id : null,
        ])->values()->all();
    }

    /**
     * @return array<int, array{recipient_type:string, recipient_id:int, recipient_name:string, email:?string, phone:?string, user_id:?int}>
     */
    private function guardians(CommunicationRequestData $request): array
    {
        $guardians = Guardian::query()
            ->where('school_id', $request->schoolId)
            ->when($request->classId || $request->sectionId, function ($q) use ($request): void {
                $q->whereHas('students.academicRecords', function ($r) use ($request): void {
                    $r->where('is_current', true)
                        ->when($request->classId, fn ($x, $c) => $x->where('class_id', $c))
                        ->when($request->sectionId, fn ($x, $s) => $x->where('section_id', $s));
                });
            })
            ->get(['id', 'name', 'email', 'phone', 'user_id']);

        return $guardians->map(fn (Guardian $g) => [
            'recipient_type' => Guardian::class,
            'recipient_id' => (int) $g->id,
            'recipient_name' => (string) $g->name,
            'email' => $g->email,
            'phone' => $g->phone,
            'user_id' => $g->user_id !== null ? (int) $g->user_id : null,
        ])->values()->all();
    }

    /**
     * @return array<int, array{recipient_type:string, recipient_id:int, recipient_name:string, email:?string, phone:?string, user_id:?int}>
     */
    private function staff(CommunicationRequestData $request): array
    {
        $staff = Staff::query()
            ->where('school_id', $request->schoolId)
            ->when($request->audienceType === AudienceType::Teachers, fn ($q) => $q->where('is_teaching', true))
            ->when($request->departmentId, fn ($q, $d) => $q->where('department_id', $d))
            ->get(['id', 'name', 'email', 'phone', 'user_id']);

        return $staff->map(fn (Staff $s) => [
            'recipient_type' => Staff::class,
            'recipient_id' => (int) $s->id,
            'recipient_name' => (string) $s->name,
            'email' => $s->email,
            'phone' => $s->phone,
            'user_id' => $s->user_id !== null ? (int) $s->user_id : null,
        ])->values()->all();
    }

    /**
     * @return array<int, array{recipient_type:string, recipient_id:int, recipient_name:string, email:?string, phone:?string, user_id:?int}>
     */
    private function custom(CommunicationRequestData $request): array
    {
        $out = [];
        foreach ($request->recipients as $r) {
            $out[] = [
                'recipient_type' => (string) ($r['recipient_type'] ?? 'custom'),
                'recipient_id' => (int) ($r['recipient_id'] ?? 0),
                'recipient_name' => (string) ($r['recipient_name'] ?? ''),
                'email' => isset($r['email']) ? (string) $r['email'] : (isset($r['address']) ? (string) $r['address'] : null),
                'phone' => isset($r['phone']) ? (string) $r['phone'] : (isset($r['address']) ? (string) $r['address'] : null),
                'user_id' => isset($r['user_id']) ? (int) $r['user_id'] : null,
            ];
        }

        return $out;
    }
}
